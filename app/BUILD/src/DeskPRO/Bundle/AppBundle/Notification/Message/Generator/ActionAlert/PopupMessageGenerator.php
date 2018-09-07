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
        /* @var $event PopupEvent */
        $event->getName();
        $messages = [];
        foreach ($this->getTargets($event) as $agent) {
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
                        $displayData['data'] = $this->getPeople(
                            $search->getMatches(),
                            $displayData['display'] === PopupModel::DISPLAY_VIEW_TYPE_DETAIL
                        );
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
                        $displayData['data'] = $this->getTickets(
                            $search->getMatches(),
                            $displayData['display'] === PopupModel::DISPLAY_VIEW_TYPE_DETAIL
                        );
                        break;
                    case PopupModel::DISPLAY_BLOCK_TYPE_ORG:
                        $search = new OrganizationSearch();
                        $search->setPersonContext($this->getUser());
                        foreach ($displayData['query'] as $query) {
                            $search->addTerm($query['term'], $query['op'], $query['val']);
                        }
                        $displayData['data'] = $this->getOrganizations(
                            $search->getMatches(),
                            $displayData['display'] === PopupModel::DISPLAY_VIEW_TYPE_DETAIL
                        );
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

    protected function getTargets(PopupEvent $event)
    {
        $data = $event->getData();
        if ($data['target']['type'] === PopupModel::TARGET_TYPE_LIST) {
            return $data['target']['list'];
        } else {
            $targetQuery = $event->getData()['target']['query'];

            $search = new PersonSearch();
            $search->setMode(PersonSearch::MODE_ANY);

            foreach ($targetQuery as $query) {
                $search->addTerm($query['term'], $query['op'], $query['val']);
            }

            return $search->getMatches();
        }
    }

    /**
     * @param array $ids
     * @param bool  $detailed
     *
     * @return Person[]|array
     */
    protected function getPeople(array $ids, $detailed = false)
    {
        $data = [];

        foreach ($this->em->getRepository(Person::class)->findBy(['id' => $ids]) as $person) {
            $context = new SideloadSerializationContext($detailed ? ['ticket', 'agent_team'] : []);
            $context->setInlineSideloads($detailed);
            $data[] = $this->serializer->toArray(
                new ApiWrapper($person),
                $context
            )['data'];
        }

        return $data;
    }

    /**
     * @param array $ids
     * @param bool  $detailed
     *
     * @return array
     */
    protected function getTickets(array $ids, $detailed = false)
    {
        $data = [];
        foreach ($this->em->getRepository(Ticket::class)->findBy(['id' => $ids]) as $ticket) {
            $context = new SideloadSerializationContext();
            $context->setInlineSideloads($detailed);
            $data[] = $this->serializer->toArray(
                new ApiWrapper($ticket),
                $context
            )['data'];
        }

        return $data;
    }

    /**
     * @param array $ids
     * @param bool  $detailed
     *
     * @return array
     */
    protected function getOrganizations(array $ids, $detailed = false)
    {
        $data = [];
        foreach ($this->em->getRepository(Organization::class)->findBy(['id' => $ids]) as $organization) {
            $context = new SideloadSerializationContext($detailed ? [] : []);
            $context->setInlineSideloads($detailed);
            $data[] = $this->serializer->toArray(
                new ApiWrapper($organization),
                $context
            )['data'];
        }

        return $data;
    }
}
