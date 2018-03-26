<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;

/**
 * Set SLA status.
 */
class SetSlaStatusAction extends AbstractAction
{
    /** @var string */
    protected $sla_status;
    /** @var int */
    protected $sla_id;

    public function __construct($sla_status, $sla_id)
    {
        $this->sla_status = $sla_status;
        $this->sla_id     = $sla_id;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        if ($this->sla_id) {
            $sla = App::getEntityRepository('DeskPRO:Sla')->find($this->sla_id);
            if (!$sla) {
                return;
            }

            $ticket_sla = $ticket->hasSla($sla);
            if (!$ticket_sla) {
                return;
            }

            $ticket_slas = [$ticket_sla];
        } else {
            $ticket_slas = $ticket->ticket_slas;
        }

        foreach ($ticket_slas as $ticket_sla) {
            $ticket_sla->setSlaStatus($this->sla_status, false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        return [
            [
                'action'     => 'set_sla_status',
                'sla_status' => $this->sla_status,
                'sla_id'     => $this->sla_id,
            ],
        ];
    }

    /**
     * @return int
     */
    public function getSlaStatus()
    {
        return $this->sla_status;
    }

    /**
     * @return int
     */
    public function getSlaId()
    {
        return $this->sla_id;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        return $otherAction;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $tr = App::getTranslator();

        // todo: phrase
        switch ($this->sla_status) {
            case 'ok':
                $value = 'OK';
                break;
            case 'warning':
                $value = 'Warning';
                break;
            case 'fail':
                $value = 'Failed';
                break;
            default: $value = '';
        }

        if ($this->sla_id) {
            $sla = App::getEntityRepository('DeskPRO:Sla')->find($this->sla_id);

            return $tr->phrase('agent.tickets.set_sla_status_for_sla_action', [
                'sla_status' => $value,
                'sla'        => $sla ? $sla->title : ('<error>Unknown #'.$this->sla_id.'</error>'),
            ]);
        } else {
            return $tr->phrase('agent.tickets.set_sla_status_action', [
                'sla_status' => $value,
            ]);
        }
    }
}
