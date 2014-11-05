<?php

namespace Application\DeskPRO\NewSearch\Manager;

use Orb\Util\Numbers;
use Orb\Validator\StringEmail;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Elasticsearch Search Manager
 */
class Elasticsearch extends ContainerAware implements SearchManagerInterface
{
    /**
     * The currently logged in person.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * Objects to search
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
     * Permission requirement
     *
     * @var array
     */
    protected $requiresPermission = array(
        'ticket'
    );

    /**
     * Search results
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
			$ent_repos = $this->container->getEm()->getRepository($model);

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

					$this->person->loadHelper('PermissionsManager');
					if (!$this->person->PermissionsManager->TicketChecker->canView($result)) {
						$result = null;
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
				'sort_type' => $sort
			));
			if ($result) {
				$this->handleResult($object, $result);
			}
        }

		foreach ($this->results as &$group) {
			$group = array_unique($group);
		}

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