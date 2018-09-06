<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Searcher\OrganizationSearch;
use Application\DeskPRO\Searcher\PersonSearch;
use Application\DeskPRO\Searcher\TicketSearch;
use DeskPRO\Bundle\AppBundle\Model\PopupModel;
use DeskPRO\Bundle\AppBundle\Notification\Event\ExternalEvent\PopupEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class PersonMessageGenerator.
 */
class PopupMessageGenerator extends AbstractGenerator
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $tokenStorage
     * @param Serializer            $serializer
     */
    public function __construct(
        EntityManager $em,
        TokenStorageInterface $tokenStorage,
        Serializer $serializer
    ) {
        parent::__construct($em, $tokenStorage);
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function createMessages(SystemEventInterface $event)
    {
        $event->getName();
        $messages = [];
        foreach ([1] as $agent) {
            $messages[] = new ActionAlert($agent, $this->getData($event), $event->getName());
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof PopupEvent;
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return array
     */
    public function getData(SystemEventInterface $event)
    {
        if ($event instanceof PopupEvent) {
            $data = $event->getData();

            foreach ($data['display'] as &$displayData) {
                switch ($displayData['type']) {
                    case PopupModel::DISPLAY_BLOCK_TYPE_PERSON:
                        $search = new PersonSearch();
                        $search->setMode(PersonSearch::MODE_ANY);
                        $search->setPersonContext($this->getUser());
                        foreach ($displayData['query'] as $query) {
                            $search->addTerm($query['term'], $query['op'], $query['val']);
                        }
                        $displayData['data'] = $this->getPeople($search->getMatches());
                        break;
                    case PopupModel::DISPLAY_BLOCK_TYPE_TICKET:
                        $search       = new TicketSearch();
                        $personSearch = new PersonSearch();
                        $personSearch->setMode(PersonSearch::MODE_ANY);
                        $personSearch->setPersonContext($this->getUser());
                        $search->setPersonContext($this->getUser());
                        $search->setPersonSearch($personSearch);
                        foreach ($displayData['query'] as $query) {
                            $search->addTerm($query['term'], $query['op'], $query['val']);
                        }
                        $displayData['data'] = $this->getTickets($search->getMatches());
                        break;
                    case PopupModel::DISPLAY_BLOCK_TYPE_ORG:
                        $search = new OrganizationSearch();
                        $search->setPersonContext($this->getUser());
                        foreach ($displayData['query'] as $query) {
                            $search->addTerm($query['term'], $query['op'], $query['val']);
                        }
                        $displayData['data'] = $this->getOrganizations($search->getMatches());
                        break;
                    default:
                        // no-op
                        break;
                }
            }

            return array_merge(
                [
                    'uuid'   => $event->getUuid(),
                    'action' => $event->getAction(),
                ],
                $data
            );
        }

        return [];
    }

    /**
     * @param $ids
     *
     * @return Person[]|array
     */
    protected function getPeople($ids)
    {
        $data = [];

        foreach ($this->em->getRepository(Person::class)->findBy(['id' => $ids]) as $person) {
            $data[] = $this->serializer->toArray(
                new ApiWrapper($person),
                new SideloadSerializationContext()
            )['data'];
        }

        return $data;
    }

    /**
     * @param $ids
     *
     * @return array
     */
    protected function getTickets($ids)
    {
        $data    = [];
        $context = new SideloadSerializationContext();
        foreach ($this->em->getRepository(Ticket::class)->findBy(['id' => $ids]) as $ticket) {
            $data[] = $this->serializer->toArray(new ApiWrapper($ticket), new SideloadSerializationContext());
        }

        return $data;
    }

    /**
     * @param $ids
     *
     * @return array
     */
    protected function getOrganizations($ids)
    {
        $data    = [];
        $context = new SideloadSerializationContext();
        foreach ($this->em->getRepository(Organization::class)->findBy(['id' => $ids]) as $organization) {
            $data[] = $this->serializer->toArray(new ApiWrapper($organization), $context);
        }

        return $data;
    }
}
