<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\DeskPRO\NewSearch\Manager;

use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Validator\StringEmail;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Elasticsearch Search Manager.
 */
class Elasticsearch implements SearchManagerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * The currently logged in person.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * Objects to search.
     *
     * @var array
     */
    protected $objects = array(
        'article'           => 'DeskPRO:Article',
        'download'          => 'DeskPRO:Download',
        'feedback'          => 'DeskPRO:Feedback',
        'news'              => 'DeskPRO:News',
        'ticket'            => 'DeskPRO:Ticket',
        'person'            => 'DeskPRO:Person',
        'organization'      => 'DeskPRO:Organization',
        'chat_conversation' => 'DeskPRO:ChatConversation',
    );

    /**
     * Permission requirement.
     *
     * @var array
     */
    protected $requiresPermission = array(
        'ticket',
    );

    /**
     * Search results.
     *
     * @var array
     */
    protected $results = array();

    public function quickSearch($q, $sort = null, array $limit_types = null)
    {
        $result_meta = array();
        $people_top  = false;

        if ($sort && !in_array($sort, array('score', 'date_active', 'date_created'))) {
            $sort = null;
        }
        if (!$sort) {
            $sort = 'score';
        }

        $repositoryManager = $this->container->get('fos_elastica.manager');

        foreach ($this->objects as $object => $model) {
            if ($limit_types !== null && !in_array($object, $limit_types)) {
                continue;
            }

            if (!$this->isAllowed($object)) {
                continue;
            }

            $repository = $repositoryManager->getRepository($model);
            $ent_repos  = $this->container->getEm()->getRepository($model);

            if ($this->requiresPermission($object)) {
                $repository->setPersonContext($this->person);
            }

            if ($model == 'DeskPRO:Ticket' && preg_match('#^[0-9A-Z\-_\.]+$#', $q)) {
                $result = $ent_repos->findTicketRef($q);
                if ($result) {
                    $this->handleResult($object, $result);
                }
            }

            if (Numbers::isInteger($q)) {
                if ($model == 'DeskPRO:Ticket' && $this->person) {
                    $result = $ent_repos->findTicketId($q);

                    if ($result) {
                        $this->person->loadHelper('PermissionsManager');
                        if (!$this->person->PermissionsManager->TicketChecker->canView($result)) {
                            $result = null;
                        }
                    }
                } else {
                    $result = $ent_repos->findById($q);
                }
                if ($result) {
                    $this->handleResult($object, $result);
                }
            }

            if ($model == 'DeskPRO:Person' && StringEmail::isValueValid($q)) {
                $result = $this->container->getSystemService('UsersourceManager')->findPersonByEmail($q);
                if ($result) {
                    $this->handleResult($object, $result);
                }
            }

            $result = $repository->find($q, null, array(
                'sort_type' => $sort,
            ));
            if ($result) {
                $this->handleResult($object, $result);
            }
        }

        $this->results = array_map(function ($group) {
            return Arrays::uniqueObjectArray($group);
        }, $this->results);

        return array($this->results, $result_meta, $people_top);
    }

    private function isAllowed($object)
    {
        switch ($object) {

            case 'person':
            case 'organization':
                return $this->person->hasPerm('agent_people.use');

            default:
                return true;

        }
    }

    private function handleResult($object, $result)
    {
        if (!isset($this->results[$object])) {
            $this->results[$object] = array();
        }

        if (!is_array($result)) {
            $result = array($result);
        }

        switch ($object) {
            default:
                $this->results[$object] = array_merge($this->results[$object], $result);
        }
    }

    private function requiresPermission($object)
    {
        return in_array($object, $this->requiresPermission);
    }

    public function setPersonContext($person)
    {
        $this->person = $person;
    }
}
