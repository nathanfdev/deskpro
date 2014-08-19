<?php

namespace Application\DeskPRO\NewSearch\Manager;

use Orb\Util\Numbers;
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
        'article'      => 'DeskPRO:Article',
        'download'     => 'DeskPRO:Download',
        'feedback'     => 'DeskPRO:Feedback',
        'news'         => 'DeskPRO:News',
        'ticket'       => 'DeskPRO:Ticket',
        'person'       => 'DeskPRO:Person',
        'organization' => 'DeskPRO:Organization'
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
    protected $results;

    public function quickSearch($q)
    {
        $result_meta = array();
        $people_top  = false;

        $repositoryManager = $this->container->get('fos_elastica.manager');

        foreach ($this->objects as $object => $model) {

            if (!$this->isAllowed($object)) {
                continue;
            }

            $repository = $repositoryManager->getRepository($model);

            if ($this->requiresPermission($object)) {
                $repository->setPersonContext($this->person);
            }

			if (Numbers::isInteger($q)) {
				$result = $repository->find('_id:' . $q);
				if ($result) {
					$this->handleResult($object, $result);
				}
			}

            $result = $repository->find($q);
			if ($result) {
				$this->handleResult($object, $result);
			}
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
        switch ($object) {

            case 'person':

                $newResult = array();

                foreach ($result as $person) {
                    $newResult[$person->id] = $person;
                    if ($person->organization) {
                        $this->results['organization'][$person->organization->id] = $person->organization;
                    }
                }

                $this->results[$object] = $result;
                break;

            case 'organization':

                $newResult = array();

                foreach ($result as $item) {
                    $newResult[$item->id] = $item;
                }

                $this->results[$object] = $result;
                break;

            default:

                $this->results[$object] = $result;
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