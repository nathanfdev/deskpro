<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Labels\LabelManager;
use DpSys\LowError\SystemErrorHandler;
use Orb\Log\Loggable;
use Orb\Log\Logger;

class ReplyActionsApplicator implements Loggable
{
    /**
     * @var \Orb\Log\Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $actions;

    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @param array            $actions
     * @param DeskproContainer $container
     */
    public function __construct(array $actions, DeskproContainer $container)
    {
        $this->actions   = $actions;
        $this->container = $container;
    }

    /**
     * @param ReplyActionsContext $context
     */
    public function apply(ReplyActionsContext $context)
    {
        foreach ($this->actions as $id => $param) {
            $this->applyAction($context, $id, $param);
        }
    }

    /**
     * @param ReplyActionsContext $context
     * @param string              $id
     * @param mixed               $param
     */
    public function applyAction(ReplyActionsContext $context, $id, $param)
    {
        $ticket  = $context->ticket;
        $message = $context->message;

        switch ($id) {
            case 'status':
                $ticket->status = $param;
                break;

            case 'is_hold':
                $ticket->is_hold = $param;
                break;

            case 'is_note':
                if ($param && $message) {
                    $message->is_agent_note = true;
                }
                break;

            case 'urgency':
                if ($param) {
                    $ticket->setUrgency($param);
                }
                break;

            case 'assign_agent':
                $ticket->agent = $param ?: null;
                break;

            case 'user':
                $ticket->person = $param;
                $ticket->person->addBrand($ticket->getBrand());
                break;

            case 'add_followers':
                if ($param && is_array($param) && !empty($param)) {
                    foreach ($param as $a) {
                        $ticket->addParticipantPerson($a);
                    }
                }
                break;

            case 'remove_followers':
                if ($param && is_array($param) && !empty($param)) {
                    foreach ($param as $a) {
                        $ticket->removeParticipantPerson($a);
                    }
                }
                break;

            case 'assign_agent_team':
                $ticket->agent_team = $param;
                break;

            case 'labels':
                foreach ($param as $l) {
                    if ($label = LabelManager::normalizeLabel($l)) {
                        $ticket->addLabelByString($l);
                    }
                }
                break;

            case 'department':
                $ticket->department = $param;
                break;

            case 'category':
                $ticket->category = $param;
                break;

            case 'priority':
                $ticket->priority = $param;
                break;

            case 'workflow':
                $ticket->workflow = $param;
                break;

            case 'product':
                $ticket->product = $param;
                break;

            case 'ticket_fields':
                $fm                = $this->container->getTicketFieldManager();
                $custom_field_data = [];
                foreach ($param as $field_id => $field_value) {
                    $custom_field_data['field_'.$field_id] = $field_value;
                }

                if ($custom_field_data) {
                    $fm->saveFormToObject($custom_field_data, $ticket, true, false);
                }
                break;
            case 'is_reply':
                // should be noop
                break;

            default:
                $e = new \InvalidArgumentException("Unknown reply action {$id}");
                SystemErrorHandler::logException($e, true, 'reply_action_'.$id);
        }
    }

    /**
     * Set the logger.
     *
     * @param \Orb\Log\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Orb\Log\Logger
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = new Logger();
        }

        return $this->logger;
    }
}
