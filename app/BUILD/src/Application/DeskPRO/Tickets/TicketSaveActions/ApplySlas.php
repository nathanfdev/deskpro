<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Slas\SlaClientMessageSender;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;

class ApplySlas implements TicketSaveActionInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Sla[]
     */
    private $slas;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var
     */
    private $cm_sender;

    /**
     * @param array                  $slas
     * @param EntityManager          $em
     * @param SlaClientMessageSender $cm_sender
     */
    public function __construct(array $slas, EntityManager $em, SlaClientMessageSender $cm_sender)
    {
        $this->slas      = Arrays::keyFromData($slas, 'id');
        $this->em        = $em;
        $this->cm_sender = $cm_sender;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($context->getEventType() != 'newticket' || !$this->slas) {
            return;
        }

        if (count($ticket->ticket_slas)) {
            $has_slas = array_map(function ($s) {
                return $s->sla->id;
            }, is_array($ticket->ticket_slas) ? $ticket->ticket_slas : $ticket->ticket_slas->toArray());
            $has_slas = array_combine($has_slas, $has_slas);
        } else {
            $has_slas = [];
        }

        $context->getLogger()->debug(sprintf('[ApplySlas] Testing %d SLAs', count($this->slas)));

        // See SetSlas.php
        // - A trigger might run to remove an SLA, but SLAs are special and apply
        // after normal triggers.
        // - But if someone made a trigger specifically to remove an SLA, the expected
        // behaviour would be that the SLA not be added (even though technically it wasnt added yet)
        // - So before adding an SLA here, look it up to make sure it wasnt subject to a trigger attempting
        // to remove it.

        $ignore_slas = $context->getVars()->get('removed_slas', []);
        $ignore_slas = array_fill_keys($ignore_slas, true);

        foreach ($this->slas as $sla) {
            if (isset($has_slas[$sla->id])) {
                $context->getLogger()->info(sprintf('[ApplySlas] SLA %d %s -- already exists', $sla->id, $sla->title));
                continue;
            }

            if (isset($ignore_slas[$sla->id])) {
                $context->getLogger()->info(sprintf('[ApplySlas] SLA %d %s -- on ignore list', $sla->id, $sla->title));
                continue;
            }

            $context->getLogger()->info(sprintf('[ApplySlas] Testing SLA %d ...', $sla->id, $sla->title));

            if ($sla->apply_type == 'all' || ($sla->apply_type == 'terms' && $sla->apply_terms->isTriggerMatch($ticket, $context))) {
                $context->getLogger()->info(sprintf('[ApplySlas] SLA %d %s -- added', $sla->id, $sla->title));
                $ticket_sla = $ticket->addSla($sla);
                $this->em->persist($ticket_sla);
                $this->cm_sender->sendMessage($ticket, $ticket_sla, $ticket_sla->sla_status, $ticket_sla->is_completed);
            } else {
                $context->getLogger()->info(sprintf('[ApplySlas] SLA %d %s -- no match', $sla->id, $sla->title));
            }
        }
    }
}
