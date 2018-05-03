<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\DependencyInjection\DeskproContainerAwareInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class ActionApplicator implements ActionApplicatorInterface
{
    /**
     * @var DeskproContainer
     */
    private $container;

    /**
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @param ActionInterface          $action
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function apply(ActionInterface $action, Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($action instanceof DeskproContainerAwareInterface || $action instanceof ContainerAwareInterface) {
            $action->setContainer($this->container);
        }

        $action->applyAction($ticket, $context);
    }
}
