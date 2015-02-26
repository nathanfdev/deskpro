<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use DeskPRO\Kernel\KernelErrorHandler;
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
        $this->actions = $actions;
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

            case 'assign_agent':
                $ticket->agent = $param ?: null;
                break;

            case 'user':
                $ticket->person = $param;
                break;

            case 'assign_agent_team':
                $ticket->agent_team = $param;
                break;

            case 'labels':
                foreach ($param as $l) {
                    $ticket->addLabelByString($l);
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
                $fm = $this->container->getTicketFieldManager();
                $custom_field_data = array();
                foreach ($param as $field_id => $field_value) {
                    $custom_field_data["field_" . $field_id] = $field_value;
                }

                if ($custom_field_data) {
                    $fm->saveFormToObject($custom_field_data, $ticket, true);
                }
                break;

            default:
                $e = new \InvalidArgumentException("Unknown reply action {$id}");
                KernelErrorHandler::logException($e, true, 'reply_action_' . $id);
        }
    }

    /**
     * Set the logger
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
