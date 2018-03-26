<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use DeskPRO\Bundle\AppBundle\DataService\AgentDataService;
use DeskPRO\Bundle\AppBundle\Notification\Event\Organization\OrganizationCreatedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class OrganizationMessageGenerator.
 */
class OrganizationMessageGenerator extends AbstractGenerator
{
    /**
     * @var AgentDataService
     */
    private $agentDataService;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $tokenStorage
     * @param AgentDataService      $agentDataService
     */
    public function __construct(
        EntityManager         $em,
        TokenStorageInterface $tokenStorage,
        AgentDataService      $agentDataService
    ) {
        parent::__construct($em, $tokenStorage);
        $this->agentDataService = $agentDataService;
    }

    /**
     * {@inheritdoc}
     */
    public function createMessages(SystemEventInterface $event)
    {
        $event->getName();
        $messages = [];
        foreach ($this->getTargets() as $agent) {
            $messages[] = new ActionAlert($agent, $this->getData($event), $event->getName());
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        if ($event instanceof OrganizationCreatedEvent) {
            return true;
        }

        return false;
    }

    /**
     * @return \int[]
     */
    public function getTargets()
    {
        return $this->agentDataService->getOnlineAgentIds();
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return array
     */
    public function getData(SystemEventInterface $event)
    {
        if ($event instanceof OrganizationCreatedEvent) {
            return [
                'type'              => $event->getEventType(),
                'organization_id'   => $event->getOrganizationId(),
                'organization_name' => $event->getOrganizationName(),
                'date_created'      => $event->getOrganizationDateCreated(),
            ];
        }

        return [];
    }
}
