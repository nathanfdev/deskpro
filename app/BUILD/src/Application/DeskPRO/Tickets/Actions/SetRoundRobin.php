<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\RoundRobin;
use Application\DeskPRO\Entity\RoundRobinLogEntry;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Set the assigned agent from Round Robin queue.
 *
 * @option int id   Round Robin id
 */
class SetRoundRobin extends AbstractContainerAwareAction implements ActionInterface, NoopableInterface
{
    protected $checked = [];

    /**
     * @param DeskproContainer $container
     */
    public function setContainer(DeskproContainer $container)
    {
        parent::setContainer($container);
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('id');

        return $options;
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\RoundRobin
     */
    protected function getRep()
    {
        return $this->getContainer()->getEm()->getRepository('DeskPRO:RoundRobin');
    }

    /**
     * @param $id
     *
     * @return null|RoundRobin
     */
    protected function getRoundRobin($id)
    {
        return $this->getRep()->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $id    = $this->getActionOption('id');
        $rr    = $this->getRoundRobin($id);
        $em    = $this->getContainer()->getEm();
        $adata = $this->getContainer()->getAgentData();

        $entry                  = new RoundRobinLogEntry();
        $entry->rr              = $rr;
        $entry['ticketId']      = $ticket['id'];
        $entry['ticketSubject'] = $ticket['subject'];
        $em->persist($entry);

        if ($agent = $rr->getNextAgent($adata, $entry)) {
            $ticket->agent = $agent;
            $rr->last      = $agent;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!(int) $this->getContainer()->getSetting('core.round_robin.enabled')) {
            return true;
        }

        if (!$rr = $this->getRoundRobin($this->getActionOption('id'))) {
            return true;
        }

        if ($rr->agents->isEmpty()) {
            return true;
        }

        return false;
    }
}
