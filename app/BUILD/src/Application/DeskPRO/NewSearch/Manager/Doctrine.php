<?php

namespace Application\DeskPRO\NewSearch\Manager;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\NewSearch\Manager\Traits\ExtractsMatchersFromQuery;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\Strings;

/**
 * Doctrine Search Manager.
 */
class Doctrine extends AbstractSearchManager implements SearchManagerInterface
{
    use ExtractsMatchersFromQuery;

    /**
     * @param null|string $query
     * @param null|string $sort
     * @param array       $limitTypes
     * @return array|mixed
     */
    public function quickSearch($query = null, $sort = null, array $limitTypes = [])
    {
        // check if we need to proceed
        if (! $this->proceedWithSearch($query)) {
            return array_map(function ($object) {
                return [$object => []];
            }, array_keys($this->objects));
        }

        // limit searchable object
        $this->limitResultingObjects($limitTypes);

        // Check for an URL first as we don't event need elastica for it
        $matchers = $this->extractMatchersFromQuery($query);
        if (count($matchers) > 0) {
            foreach ($this->extractMatchersFromQuery($query) as $matcher) {
                // check if person has access to an object
                if (!$this->isAllowed($matcher['object'])) {
                    continue;
                }

                // as we operate only with ids, refs and slugs, we don't need elasticsearch here
                $entityRepository = $this->getEntityManager()
                    ->getRepository($this->objects[$matcher['object']]);

                /**
                 * For tickets search we use custom logic in order to utilize
                 * findTicketRef(), SearchTicketRef() and findTicketId() methods,
                 * and check ticket permissions for logged in user if any.
                 *
                 * @see AbstractSearchManager::getTicketByRefOrId()
                 */
                if ($matcher['object'] === 'ticket') {
                    $this->getTicketByRefOrId($entityRepository, $matcher);
                } else {
                    /**
                     * All other objects could simply found by id or slug,
                     * so no specific logic needed here.
                     */
                    $entity = $entityRepository->findOneBy(
                        [
                            $matcher['field'] => $matcher['param']
                        ]
                    );

                    /**
                     * Add to results set if it's not null
                     *
                     * @todo: maybe better use instanceof, but this will
                     *        require more complex workaround, so not sure
                     *        it does matter that much to impact the timings.
                     */
                    if (!is_null($entity)) {
                        $this->handleResult($matcher['object'], $entity);
                    }
                }
            }

            return [$this->prepareResults(), [], false];
        }

        // backward compatibility
        // @fixme: remove after refactoring
        $q           = $query;
        $this->em    = $this->getEntityManager();
        $type_to_ent = $this->objects;

        // Old doctrine manager code manager
        // @todo: badly requires rafactoring, as it's very buggy

        $results = [
            'article'           => [],
            'download'          => [],
            'feedback'          => [],
            'news'              => [],
            'ticket'            => [],
            'person'            => [],
            'organization'      => [],
            'chat_conversation' => [],
            'topic'             => [],
        ];
        if (!$this->person->hasPerm('agent_people.use')) {
            unset($type_to_ent['person']);
            unset($type_to_ent['organization']);
        }
        $result_meta = [];
        $people_top  = false;


        //------------------------------
        // ID based
        //------------------------------

        $is_label = false;

        if (preg_match('#^\[(.*?)\]$#', $q, $m)) {
            $is_label = $m[1];
        }

        // We dont know about past ref formats, so just always try to find
        // a ref if its a valid form
        if (preg_match('#^[0-9A-Z\-_\.]+$#', $q)) {
            $ticket = $this->em->getRepository('DeskPRO:Ticket')->findTicketRef(strtoupper($q));
            if ($ticket) {
                if ($ticket && $this->person->PermissionsManager->TicketChecker->canView($ticket)) {
                    $results['ticket'][] = $ticket;
                }
            } elseif (strlen($q) >= 3) {
                $tickets = $this->em->getRepository('DeskPRO:Ticket')->searchTicketRef($q);
                foreach ($tickets as $ticket) {
                    if ($ticket && $this->person->PermissionsManager->TicketChecker->canView($ticket)) {
                        $results['ticket'][] = $ticket;
                    }
                }
            }
        }

        // See if its a tac code (from the URL in user emails)
        $info = \Application\DeskPRO\Entity\Ticket::decodeAccessCode($q);
        if (!empty($info['ticket_id']) && $info['ticket_id']) {
            $ticket = $this->em->getRepository('DeskPRO:Ticket')->find($info['ticket_id']);
            if ($ticket && $this->person->PermissionsManager->TicketChecker->canView($ticket)) {
                $results['ticket'][] = $ticket;
            }
        }

        // Subject search against tickets
        $words = Strings::utf8_strtolower($q);
        $words = explode(' ', $words);
        $words = Arrays::removeFalsey($words);
        $words = array_unique($words);
        $words = array_filter($words, function ($s) {
            if (strlen($s) >= 3) {
                return true;
            } else {
                return false;
            }
        });

        $after_id = $this->container->getDbRead()->fetchColumn('SELECT id FROM tickets ORDER BY id DESC');
        $after_id = $after_id - 10000;

        if ($words) {
            $db = $this->container->getDbRead();

            //------------------------------
            // Ticket Subject
            //------------------------------

            $where = [];
            foreach ($words as $w) {
                $where[] = '(subject LIKE '.$db->quote('%'.str_replace(['%', '_'], ['\\%', '\\_'], $w).'%').')';
            }

            $where[] = "(id > $after_id)";

            $where = implode(' AND ', $where);

            $ticket_ids = $this->container->getDbRead()->fetchAllCol("
                SELECT id
                FROM tickets
                WHERE $where
                ORDER BY id DESC
                LIMIT 100
            ");

            if ($ticket_ids) {
                $tickets = $this->em->getRepository('DeskPRO:Ticket')->getByIds($ticket_ids, true);
                foreach ($tickets as $ticket) {
                    if ($ticket && $this->person->PermissionsManager->TicketChecker->canView($ticket)) {
                        $results['ticket'][] = $ticket;
                    }
                }
            }

            //------------------------------
            // Titles
            //------------------------------

            $where = [];
            foreach ($words as $w) {
                $where[] = '(title LIKE '.$db->quote('%'.str_replace(['%', '_'], ['\\%', '\\_'], $w).'%').')';
            }
            $where[] = "(status != 'hidden')";
            $where   = implode(' AND ', $where);

            foreach ([
                         'article'  => 'articles',
                         'download' => 'downloads',
                         'feedback' => 'feedback',
                         'news'     => 'news',
                         'topic'    => 'topics',
                     ] as $type => $table) {
                $ids = $this->container->getDbRead()->fetchAllCol("
                    SELECT id
                    FROM $table
                    WHERE $where
                    ORDER BY id DESC
                    LIMIT 25
                ");

                if ($ids) {
                    $results[$type] = $this->em->getRepository($type_to_ent[$type])->getByIds($ids, true);
                }
            }
        }

        if (!$is_label && Numbers::isInteger($q)) {
            foreach ($type_to_ent as $type => $ent) {
                if ($type == 'ticket') {
                    $obj = $this->em->getRepository('DeskPRO:Ticket')->findTicketId($q);
                    if ($obj && $obj->getId() != $q) {
                        $result_meta['ticket_deleted']       = $obj->getId();
                        $result_meta['ticket_deleted_oldid'] = $q;
                    }
                } else {
                    $obj = $this->em->find($ent, $q);
                }
                if ($obj) {
                    if ($obj instanceof \Application\DeskPRO\Entity\Ticket && !$this->person->PermissionsManager->TicketChecker->canView($obj)) {
                        continue;
                    }
                    $results[$type][] = $obj;
                }
            }
        } else {
            if ($this->person->hasPerm('agent_people.use')) {
                if (!$is_label) {
                    //------------------------------
                    // Email address: Full or partial
                    //------------------------------

                    if (preg_match('#^\S*@\S*$#', $q)) {
                        $people_top = true;
                        $people     = [];

                        // Complete email address
                        if (\Orb\Validator\StringEmail::isValueValid($q)) {
                            $p      = $this->container->getSystemService('UsersourceManager')->findPersonByEmail($q);
                            $people = [];
                            if ($p) {
                                $people[] = $p;
                            }
                        } else {
                            if (strpos($q, '@') === 0) {
                                $email = substr($q, 1);
                                $email = str_replace(['%', '_'], ['\\\\%', '\\\\_'], $email).'%';

                                if ($this->settings->get('core_tablecounts.people') < 150000) {
                                    $people_ids = $this->container->getDbRead()->fetchAllCol('
                                        SELECT people.id
                                        FROM people
                                        JOIN people_emails ON (people_emails.person_id = people.id)
                                        WHERE people_emails.email_domain LIKE ?
                                        ORDER BY people.id DESC
                                        LIMIT 15
                                    ', [$email]);
                                } else {
                                    $people_ids = $this->container->getDbRead()->fetchAllCol('
                                        SELECT people.id
                                        FROM people
                                        JOIN tickets ON (tickets.person_id = people.id)
                                        JOIN people_emails ON (people_emails.person_id = people.id)
                                        WHERE
                                            tickets.id > ?
                                            AND people_emails.email_domain LIKE ?
                                        ORDER BY tickets.id DESC
                                        LIMIT 15
                                    ', [$after_id, $email]);
                                }
                            } else {
                                $email = str_replace(['%', '_'], ['\\\\%', '\\\\_'], $q).'%';

                                if ($this->settings->get('core_tablecounts.people') < 150000) {
                                    $people_ids = $this->container->getDbRead()->fetchAllCol('
                                        SELECT people.id
                                        FROM people
                                        JOIN people_emails ON (people_emails.person_id = people.id)
                                        WHERE people_emails.email LIKE ?
                                        ORDER BY people.id DESC
                                        LIMIT 15
                                    ', [$email]);
                                } else {
                                    $people_ids = $this->container->getDbRead()->fetchAllCol('
                                        SELECT people.id
                                        FROM people
                                        JOIN tickets ON (tickets.person_id = people.id)
                                        JOIN people_emails ON (people_emails.person_id = people.id)
                                        WHERE
                                            tickets.id > ?
                                            AND people_emails.email LIKE ?
                                        ORDER BY tickets.id DESC
                                        LIMIT 15
                                    ', [$after_id, $email]);
                                }
                            }

                            if ($people_ids) {
                                $people = $this->em->getRepository('DeskPRO:Person')->getByIds($people_ids, true);
                            }
                        }

                        foreach ($people as $p) {
                            $results['person'][$p->id] = $p;

                            if ($p->organization) {
                                $results['organization'][$p->organization->id] = $p->organization;
                            }
                        }

                        //------------------------------
                        // Search for string match in name or email
                        //------------------------------
                    } else {
                        $people = [];

                        $q        = preg_replace('#\s+#', ' ', $q);
                        $q_search = '%'.str_replace(['%', '_'], ['\\\\%', '\\\\_'], $q).'%';

                        if ($this->settings->get('core_tablecounts.people') < 150000) {
                            $people_ids = $this->container->getDbRead()->fetchAllCol("
                                SELECT people.id
                                FROM people
                                JOIN people_emails ON (people_emails.person_id = people.id)
                                WHERE
                                    people.name LIKE ?
                                    OR people.first_name LIKE ?
                                    OR people.last_name LIKE ?
                                    OR people_emails.email LIKE ?
                                    OR CONCAT_WS(' ' , people.first_name, people.last_name) LIKE ?
                                ORDER BY people.id DESC
                                LIMIT 15
                            ", [$q_search, $q_search, $q_search, $q_search, $q_search]);
                        } else {
                            $people_ids = $this->container->getDbRead()->fetchAllCol("
                                SELECT people.id
                                FROM people
                                JOIN tickets ON (tickets.person_id = people.id)
                                JOIN tickets_participants ON (tickets_participants.person_id = people.id)
                                JOIN people_emails ON (people_emails.person_id = people.id)
                                WHERE
                                    (tickets.id > ? OR tickets_participants.ticket_id > ?)
                                    AND (
                                        people.name LIKE ?
                                        OR people.first_name LIKE ?
                                        OR people.last_name LIKE ?
                                        OR people_emails.email LIKE ?
                                        OR CONCAT_WS(' ' , people.first_name, people.last_name) LIKE ?
                                    )
                                ORDER BY tickets.id DESC
                                LIMIT 15
                            ", [$after_id, $after_id, $q_search, $q_search, $q_search, $q_search, $q_search]);
                        }

                        if ($people_ids) {
                            $people = $this->em->getRepository('DeskPRO:Person')->getByIds($people_ids, true);
                        }

                        foreach ($people as $p) {
                            $results['person'][$p->id] = $p;

                            if ($p->organization) {
                                $results['organization'][$p->organization->id] = $p->organization;
                            }
                        }

                        // Organizations
                        $orgs = $this->em->getRepository('DeskPRO:Organization')->search($q, 25);
                        $oids = [];
                        foreach ($orgs as $o) {
                            $results['organization'][$o->id] = $o;
                            $oids[]                          = $o->getId();
                        }

                        if ($oids) {
                            // Fetch users of these orgs too
                            $people = $this->em->createQuery('
                                SELECT p
                                FROM DeskPRO:Person p
                                WHERE p.organization IN (?0)
                                ORDER BY p.date_last_login DESC, p.id DESC
                            ')->setMaxResults(100)->execute([$oids]);
                            foreach ($people as $p) {
                                $results['person'][$p->id] = $p;
                            }
                        }
                    }
                } // is label
            } // agent_people.use perm

            //------------------------------
            // Labels
            //------------------------------

            $label_search = new \Application\DeskPRO\Labels\LabelSearch($this->em);

            $search_types = ['article', 'download', 'feedback', 'news', 'ticket'];

            if ($this->person->hasPerm('agent_people.use')) {
                $search_types[] = 'organization';
                $search_types[] = 'person';
            }

            $label_results = $label_search->search($is_label ? $is_label : $q);

            if ($label_results) {
                foreach ($label_results as $type => $type_results) {
                    foreach ($type_results as $res) {
                        $results[$type][] = $res;
                    }
                }
            }

            if (App::getConfig('enable_twitter') && count($this->person->getTwitterAccountIds()) && preg_match('/^@[a-z0-9_]+$/i', $q)) {
                $results['twitter'] = $q;
            }
        }

        return [$results, $result_meta, $people_top];
    }
}
