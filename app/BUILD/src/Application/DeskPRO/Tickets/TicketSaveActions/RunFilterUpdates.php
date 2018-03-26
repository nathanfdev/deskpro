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
use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\DiffEnv;
use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\FilterDiffer;
use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\TicketChange;
use DeskPRO\Bundle\AppBundle\TicketFilters\Loader;
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
     * @var \DeskPRO\Bundle\AppBundle\Notification\NotificationEventManager
     */
    private $eventManager;

    /**
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container    = $container;
        $this->em           = $this->container->get('doctrine.orm.default_entity_manager');
        $this->eventManager = $container->get('deskpro.notification.event_manager');
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

        if ($this->container->get('deskpro.feature_flags')->hasBeta('new_filters')) {
            $this->processTicketNewFilters($ticket, $context);

            return;
        }

        /** @var \Application\DeskPRO\EntityRepository\Person $personRepo */
        $personRepo     = $this->em->getRepository(Person::class);
        $onlineAgentIds = $personRepo->getActiveAgents(true);

        $detector = $this->container->getTicketFilterChangeDetector();
        $detector->getFilterChangeSet($ticket, $context, $onlineAgentIds)->getListUpdateClientMessages($onlineAgentIds);
    }

    private function processTicketNewFilters(Ticket $ticket, ExecutorContextInterface $context)
    {
        $loader  = $this->container->get('ticketfilters.loader');
        $matcher = $this->container->get('ticketfilters.ticket_matcher');
        $diffEnv = new DiffEnv($matcher, $loader->getAgents(), $loader->getFilters());
        $differ  = new FilterDiffer($diffEnv);

        list($ticketA, $ticketB) = $ticket->getStateChangeRecorder()->getBeforeAfterModels();

        $ticketChange = new TicketChange($ticketA, $ticketB);
        $filterOps    = $differ->getFilterChangeOperations($ticketChange);

        $eventOps = [];
        foreach ($filterOps as $op) {
            $add = $op->getAddAgentIds();
            $del = $op->getDelAgentIds();

            if ($add) {
                $eventOps[] = [
                    'op'        => 'add',
                    'filter_id' => $op->getFilterId(),
                    'targets'   => $add,
                    'version'   => '2',
                ];
            }
            if ($del) {
                $eventOps[] = [
                    'op'        => 'add',
                    'filter_id' => $op->getFilterId(),
                    'targets'   => $add,
                    'version'   => '2',
                ];
            }
        }

        if ($eventOps) {
            $this->eventDispatcher->dispatch(
                TicketUpdatedEvent::EVENT_NAME,
                new TicketUpdatedEvent(
                    'agent.filter-update',
                    [
                        'ticket_id'  => $ticket->getId(),
                        'operations' => $eventOps,
                    ]
                )
            );
            $this->eventManager->deliver(true);
        }
    }
}
