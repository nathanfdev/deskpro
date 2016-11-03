<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
