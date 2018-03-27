<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class RunFilterUpdates.
 */
class RunFilterUpdates implements TicketSaveActionInterface, ErrorCheckedInterface
{
    /**
     * @var DeskproContainer
     */
    protected $container;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
        $this->em        = $this->container->get('doctrine.orm.default_entity_manager');
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($context->getEventType() == 'noop') {
            $context->getLogger()->info('[RunFilterUpdates] None (noop)');

            return;
        }

        /** @var \Application\DeskPRO\EntityRepository\Person $personRepo */
        $personRepo     = $this->em->getRepository(Person::class);
        $onlineAgentIds = $personRepo->getActiveAgents(true);

        $detector = $this->container->getTicketFilterChangeDetector();
        $detector->getFilterChangeSet($ticket, $context, $onlineAgentIds)->getListUpdateClientMessages($onlineAgentIds);
    }
}
