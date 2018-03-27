<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Slas\SlaClientMessageSender;
use Orb\Util\CheckedOptionsArray;

/**
 * Adds or removes SLAs on a ticket.
 *
 * @option int[] add_sla_ids     Array of SLA IDs to add
 * @option int[] remove_sla_ids  Array of SLA IDs to remove
 */
class SetSlas extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('add_sla_ids', 'remove_sla_ids');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $em          = $this->getContainer()->getEm();
        $ticket_slas = $this->getContainer()->getSystemService('ticket_slas');

        $cm_sender = new SlaClientMessageSender(
            $this->getContainer()->getDb(),
            $this->getContainer()->get('event_dispatcher')
        );

        //--------------------
        // Add SLAs
        //--------------------

        if ($add_sla_ids = $this->getActionOption('add_sla_ids')) {
            foreach ($add_sla_ids as $sla_id) {
                $sla = $ticket_slas->getById($sla_id);
                if (!$sla) {
                    $context->getLogger()->debug(sprintf('[SetSlas] Skip add %d, does not exist', $sla_id));
                    continue;
                }

                if ($ticket->hasSla($sla)) {
                    $context->getLogger()->debug(sprintf('[SetSlas] Skip add %d, already on ticket', $sla_id));
                } else {
                    $ticket_sla = $ticket->addSla($sla);
                    $em->persist($ticket_sla);
                    $context->getLogger()->debug(sprintf('[SetSlas] Add %d', $sla_id));
                    $cm_sender->sendMessage($ticket, $ticket_sla, $ticket_sla->sla_status, $ticket_sla->is_completed);
                }
            }
        }

        //--------------------
        // Remove SLAs
        //--------------------

        if ($remove_sla_ids = $this->getActionOption('remove_sla_ids')) {
            $removed_ids = $context->getVars()->get('removed_slas', []);

            foreach ($remove_sla_ids as $sla_id) {
                $sla = $ticket_slas->getById($sla_id);
                if (!$sla) {
                    $context->getLogger()->debug(sprintf('[SetSlas] Skip remove %d, does not exist', $sla_id));
                    continue;
                }

                $removed_ids[] = $sla->id;

                if (!$ticket->hasSla($sla)) {
                    $context->getLogger()->debug(sprintf('[SetSlas] Skip remove %d, not on ticket', $sla_id));
                } else {
                    $ticket_sla = $ticket->removeSla($sla);
                    $em->remove($ticket_sla);
                    $context->getLogger()->debug(sprintf('[SetSlas] Remove %d', $sla_id));
                    $cm_sender->sendMessage($ticket, $ticket_sla, $ticket_sla->sla_status, $ticket_sla->is_completed);
                }
            }

            // These are saved so it can be used in ApplySlas.php
            $context->getVars()->set('removed_slas', $removed_ids);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'slas')) {
            return ['slas'];
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}

// xx bytes to prevent 4096 filesize (php bug)
