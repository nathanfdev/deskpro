<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;

/**
 * Adds SLA.
 */
class AddSlaAction extends AbstractAction
{
    /** @var array */
    protected $sla_ids = [];

    public function __construct($sla_id)
    {
        $this->sla_ids = (array) $sla_id;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        if (!$this->sla_ids) {
            return;
        }

        foreach ($this->sla_ids as $sla_id) {
            $sla = App::getEntityRepository('DeskPRO:Sla')->find($sla_id);
            if ($sla) {
                $ticket->addSla($sla);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        return [
            ['action' => 'add_sla', 'sla_ids' => $this->sla_ids],
        ];
    }

    /**
     * @return array
     */
    public function getSlaIds()
    {
        return $this->sla_ids;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        $this->sla_ids = array_merge($this->sla_ids, $otherAction->getSlaIds());
        $this->sla_ids = array_unique($this->sla_ids);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $tr   = App::getTranslator();
        $slas = App::getEntityRepository('DeskPRO:Sla')->getByIds($this->sla_ids);

        $titles = [];
        foreach ($slas as $sla) {
            $titles[$sla->id] = $as_html ? htmlspecialchars($sla->title) : $sla->title;
        }

        foreach ($this->sla_ids as $id) {
            if (!isset($titles[$id])) {
                $titles[$id] = "<error>Unknown #$id</error>";
            }
        }

        return $tr->phrase('agent.tickets.add_sla_action', ['sla' => $titles ? implode(', ', $titles) : '[unknown]']);
    }
}
