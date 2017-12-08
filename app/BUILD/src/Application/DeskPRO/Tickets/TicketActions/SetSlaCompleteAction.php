<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;

/**
 * Set SLA complete.
 */
class SetSlaCompleteAction extends AbstractAction
{
    /** @var array */
    protected $actions = [];

    public function __construct($sla_complete, $sla_id)
    {
        $this->actions = [$sla_complete => [$sla_id]];
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        foreach ($this->actions as $complete => $sla_ids) {
            if (in_array('0', $sla_ids)) {
                // take action for all
                $sla_ids = ['0'];
            }
            foreach ($sla_ids as $sla_id) {
                if ($sla_id) {
                    $sla = App::getEntityRepository('DeskPRO:Sla')->find($sla_id);
                    if (!$sla) {
                        continue;
                    }

                    $ticket_sla = $ticket->hasSla($sla);
                    if (!$ticket_sla) {
                        continue;
                    }

                    $ticket_slas = [$ticket_sla];
                } else {
                    $ticket_slas = $ticket->ticket_slas;
                }

                foreach ($ticket_slas as $ticket_sla) {
                    $ticket_sla->setIsCompletedSet($complete);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        return [
            ['action' => 'set_sla_complete', 'actions' => $this->actions],
        ];
    }

    /**
     * @return array
     */
    public function getSlaActions()
    {
        return $this->actions;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        $actions = $otherAction->getSlaActions();
        foreach ($actions as $complete => $sla_ids) {
            if (isset($this->actions[$complete])) {
                $this->actions[$complete] = array_merge($this->actions[$complete], $sla_ids);
                $this->actions[$complete] = array_unique($this->actions[$complete]);
            } else {
                $this->actions[$complete] = $sla_ids;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $parts = [];
        foreach ($this->actions as $complete => $sla_ids) {
            if (in_array('0', $sla_ids)) {
                // take action for all
                $titles = null;
            } else {
                $slas   = App::getEntityRepository('DeskPRO:Sla')->getByIds($sla_ids);
                $titles = [];
                foreach ($slas as $s) {
                    $titles[$s->id] = $as_html ? htmlspecialchars($s->title) : $s->title;
                }

                foreach ($sla_ids as $id) {
                    if (!isset($titles[$id])) {
                        $titles[$id] = "<error>Unknown #$id</error>";
                    }
                }
            }

            if ($complete) {
                if ($titles !== null) {
                    $parts[] = 'Set SLA requirements to complete for SLA '.($titles ? implode($titles, ', ') : '[unknown]');
                } else {
                    $parts[] = 'Set SLA requirements to complete';
                }
            } else {
                if ($titles !== null) {
                    $parts[] = 'Set SLA requirements to incomplete for SLA '.($titles ? implode($titles, ', ') : '[unknown]');
                } else {
                    $parts[] = 'Set SLA requirements to incomplete';
                }
            }
        }

        return implode('; ', $parts);
    }
}
