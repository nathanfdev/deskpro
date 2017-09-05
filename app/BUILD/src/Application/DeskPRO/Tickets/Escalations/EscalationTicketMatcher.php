<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Escalations;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketEscalation;
use Application\DeskPRO\Entity\TicketSearchActive;
use Application\DeskPRO\Searcher\OrganizationSearch;
use Application\DeskPRO\Searcher\PersonSearch;
use Application\DeskPRO\Searcher\TicketSearch;
use DeskPRO\Component\Util\DebugUtils;
use DeskPRO\Component\Util\ListUtils;
use Doctrine\ORM\EntityManager;
use DpSys\LowError\SystemErrorHandler;
use Monolog\Logger;
use Psr\Log\NullLogger;

class EscalationTicketMatcher
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var bool
     */
    private $doubleCheck = false;

    public function __construct(EntityManager $em, Connection $db)
    {
        $this->em     = $em;
        $this->db     = $db;
        $this->logger = new NullLogger();
    }

    /**
     * Enable double checking the ticket against search criteria. This is
     * to identify tickets that get matched due to mis-matching
     * ticket_search_active results.
     */
    public function enableDoubleCheck()
    {
        $this->doubleCheck = true;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param TicketEscalation $esc
     * @param int              $limit
     *
     * @return \Application\DeskPRO\Entity\Ticket[]
     */
    public function getMatches(TicketEscalation $esc, $limit = 100)
    {
        $this->logger->debug(sprintf('[EscalationTicketMatcher] Getting matches for %d %s -- limit(%d)', $esc->id, $esc->title, $limit));

        $ms_start = microtime(true);

        $searcher = $this->_getSearcherForEscalation($esc);
        $searcher->setLimit($limit);

        $this->logger->debug(sprintf('[EscalationTicketMatcher] --> SQL: %s', $searcher->getSql()));
        $ticket_ids = $searcher->getMatches();
        $tickets    = [];
        if ($ticket_ids) {
            $tickets = $this->em->getRepository(Ticket::class)->getByIds($ticket_ids);
        }
        if ($ticket_ids) {
            $this->logger->debug(sprintf('[EscalationTicketMatcher] --> TicketIDs: %s', implode(', ', $ticket_ids)));
        }
        $this->logger->debug(sprintf('[EscalationTicketMatcher] --> Number of results: %d', count($tickets)));
        $this->logger->debug(sprintf('[EscalationTicketMatcher] --> Took %.4fs', microtime(true) - $ms_start));

        if ($this->doubleCheck && $tickets) {
            $tickets = $this->doubleCheckResults($esc, $tickets);
            $this->logger->debug(sprintf('[EscalationTicketMatcher] --> Number of results after filter: %d', count($tickets)));
        }

        return $tickets;
    }

    /**
     * @param TicketEscalation $esc
     * @param Ticket[]         $tickets
     *
     * @return Ticket[]
     */
    private function doubleCheckResults(TicketEscalation $esc, array $tickets)
    {
        $ms_start = microtime(true);
        $this->logger->debug('[EscalationTicketMatcher] Double-checking matched tickets');

        $ids = ListUtils::map($tickets, function (Ticket $t) {
            return $t->getId();
        });
        $cols = TicketSearchActive::getFieldNamesAsSqlString();

        $sql = "(SELECT 'real' AS result_type, $cols FROM tickets WHERE id IN (?))";
        $sql .= "\nUNION\n";
        $sql .= "(SELECT 'search' AS result_type, $cols FROM tickets_search_active WHERE id IN (?))";

        $results = $this->db->fetchAll(
            $sql,
            [$ids, $ids],
            [\Doctrine\DBAL\Connection::PARAM_INT_ARRAY, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY]
        );

        $realResults   = [];
        $searchResults = [];

        foreach ($results as $r) {
            $type = $r['result_type'];
            unset($r['result_type']);

            switch ($type) {
                case 'real':   $realResults[$r['id']]   = $r; break;
                case 'search': $searchResults[$r['id']] = $r; break;
                default: throw new \RuntimeException();
            }
        }

        $removeIds = [];

        foreach ($searchResults as $searchResult) {
            if (!isset($realResults[$searchResult['id']])) {
                continue;
            }
            $realResult = $realResults[$searchResult['id']];
            $diff       = array_diff($searchResult, $realResult);
            if ($diff) {
                $e = new \UnexpectedValueException(sprintf(
                    "[EscalationTicketMatcher] <TicketEscalation:%d> Ticket:%d mismatch -- %s\n\tReal: %s\n\tSearch: %s",
                    $esc->getId(),
                    $searchResult['id'],
                    DebugUtils::varToString($diff),
                    DebugUtils::varToString($realResult),
                    DebugUtils::varToString($searchResult)
                ));
                SystemErrorHandler::logException($e);
                $removeIds[] = $searchResult['id'];
            }
        }

        $this->logger->debug(sprintf('[EscalationTicketMatcher] --> Done check in %.4fs', microtime(true) - $ms_start));
        if ($removeIds) {
            $this->logger->debug(sprintf('[EscalationTicketMatcher] --> TicketIDs: %s', implode(', ', $removeIds)));
        }
        $this->logger->debug(sprintf('[EscalationTicketMatcher] --> Number of mismatching tickets: %d', count($removeIds)));

        // we will skip results in this iteration if there are mismatches,
        // if its a false positive (e.g. race condition), then it'll be picked up
        // in the next run anyway
        if ($removeIds) {
            $tickets = ListUtils::filter($tickets, function (Ticket $t) use ($removeIds) {
                return !in_array($t->getId(), $removeIds);
            });

            $db = $this->db;
            \DpShutdown::add(
                function () use ($removeIds, $cols, $db) {
                    foreach ($removeIds as $id) {
                        try {
                            $data = $db->fetchAssoc("SELECT {$cols} FROM tickets WHERE id = ?", [$id]);
                            if ($data && $data['status'] != 'archived') {
                                $db->replace('tickets_search_active', $data);
                            } else {
                                $db->delete('tickets_search_active', ['id' => $id]);
                            }
                        } catch (\Exception $e) {
                            SystemErrorHandler::logException($e);
                        }
                    }
                }, null, 'db_done_trans_commit'
            );

            return $tickets;
        }

        return $tickets;
    }

    /**
     * @param TicketEscalation $esc
     *
     * @throws \InvalidArgumentException
     *
     * @return TicketSearch
     */
    private function _getSearcherForEscalation(TicketEscalation $esc)
    {
        $searcher = new TicketSearch();
        $searcher->addTerm('escalation_eliminator', 'is', ['escalation' => $esc]);

        // set this efficient order to make sure th default (status/urgency) isnt used
        $searcher->setOrderBy('ticket.date_created');

        $user_searcher = new PersonSearch();
        $org_searcher  = new OrganizationSearch();

        $has_user_terms = false;
        $has_org_terms  = false;

        if ($esc->terms) {
            foreach ($esc->terms as $term) {
                if ($term['op'] != 'ignore') {
                    if (strpos($term['type'], 'person_') === 0) {
                        $user_searcher->addTerm($term['type'], $term['op'], $term['options']);
                        $has_user_terms = true;
                    } elseif (strpos($term['type'], 'org_') === 0) {
                        $org_searcher->addTerm($term['type'], $term['op'], $term['options']);
                        $has_org_terms = true;
                    } else {
                        $searcher->addTerm($term['type'], $term['op'], $term['options']);
                    }
                }
            }
        }
        if ($esc->terms_any) {
            foreach ($esc->terms_any as $term) {
                if ($term['op'] != 'ignore') {
                    if (strpos($term['type'], 'person_') === 0) {
                        $user_searcher->addAnyTerm($term['type'], $term['op'], $term['options']);
                        $has_user_terms = true;
                    } elseif (strpos($term['type'], 'org_') === 0) {
                        $org_searcher->addAnyTerm($term['type'], $term['op'], $term['options']);
                        $has_org_terms = true;
                    } else {
                        $searcher->addAnyTerm($term['type'], $term['op'], $term['options']);
                    }
                }
            }
        }

        if ($has_user_terms) {
            $searcher->setPersonSearch($user_searcher);
        }
        if ($has_org_terms) {
            $searcher->setOrganizationSearch($org_searcher);
        }

        $searcher->addRawWhere("tickets.date_created >= '".$esc->date_created->format('Y-m-d H:i:s')."'");

        $time_secs = $esc->event_trigger_time;
        switch ($esc->event_trigger) {
            case TicketEscalation::EVENT_TYPE_TIME_OPEN:
                $searcher->addRawWhere('tickets.status IN (\'awaiting_user\', \'awaiting_agent\')');
                $date_cut = new \DateTime('-'.$time_secs.' seconds');
                $searcher->addTerm('date_created', 'lte', ['date1' => $date_cut]);

                break;

            case TicketEscalation::EVENT_TYPE_TIME_USER_WAITING:
                $searcher->addTerm('status', 'is', ['awaiting_agent']);
                $searcher->addRawWhere('tickets.date_user_waiting IS NOT NULL');

                $date_cut = new \DateTime('-'.$time_secs.' seconds');
                $searcher->addTerm('user_waiting', 'lte', ['date1' => $date_cut]);

                break;

            case TicketEscalation::EVENT_TYPE_TIME_TOTAL_USER_WAITING:
                $searcher->addTerm('status', 'is', ['awaiting_agent']);
                $searcher->addRawWhere('tickets.date_user_waiting IS NOT NULL');
                $searcher->addTerm('total_user_waiting', 'between', [$time_secs, $time_secs]);
                break;

            case TicketEscalation::EVENT_TYPE_TIME_AGENT_WAITING:
                $searcher->addTerm('status', 'is', ['awaiting_user']);
                $searcher->addRawWhere('tickets.date_agent_waiting IS NOT NULL');

                $date_cut = new \DateTime('-'.$time_secs.' seconds');
                $searcher->addTerm('agent_waiting', 'lte', ['date1' => $date_cut]);

                break;

            case TicketEscalation::EVENT_TYPE_TIME_RESOLVED:
                $searcher->addTerm('status', 'is', ['resolved']);
                $searcher->addRawWhere('tickets.date_resolved IS NOT NULL');

                $date_cut = new \DateTime('-'.$time_secs.' seconds');
                $searcher->addTerm('date_resolved', 'lte', ['date1' => $date_cut]);

                break;

            case TicketEscalation::EVENT_TYPE_TIME_ON_HOLD:
                $searcher->addTerm('status', 'is', ['awaiting_agent']);
                $searcher->addTerm('date_on_hold', 'lte', ['date1' => new \DateTime('-'.$time_secs.' seconds')]);

                break;

            default:
                throw new \InvalidArgumentException('Invalid escalation event: '.$esc->event_trigger);
        }

        return $searcher;
    }
}
