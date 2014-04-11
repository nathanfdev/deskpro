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
     * Elasticsearch Finders
     *
     * @var array
     */
    protected $finders = array(
        'article',
        'download',
        'feedback',
        'news',
        'person',
        'organization',
        'ticket'
    );

    public function quickSearch($q)
    {
        $results     = array();
        $result_meta = array();
        $people_top  = false;

        foreach ($this->finders as $finder) {

            if (!$this->isAllowed($finder)) {
                continue;
            }

            $finderService = $this->container->get(sprintf('fos_elastica.finder.deskpro.%s', $finder));

            if (Numbers::isInteger($q)) {
                $result = $finderService->find('_id:' . $q);
            } else {
                $result = $finderService->find($q);
            }

            $results[$finder] = $this->handleResult($finder, $result);

        }

        foreach ($results['ticket'] as $index => $ticket) {
            if ($this->person->PermissionsManager->TicketChecker->canView($ticket) === false) {
                unset ($results['ticket'][$index]);
            }
        }

        return array($results, $result_meta, $people_top);
    }

    private function isAllowed($finder)
    {
        switch ($finder) {

            case 'person':
            case 'organization':
                return $this->person->hasPerm('agent_people.use');

            default:
                return true;

        }
    }

    private function handleResult($finder, $result)
    {
        switch ($finder) {

            case 'person':
            case 'organization':

                $newResult = array();

                foreach ($result as $item) {
                    $newResult[$item->id] = $item;
                }

                return $newResult;

            default:

                return $result;
        }
    }

    public function setPersonContext($person)
    {
        $this->person = $person;
    }
} 