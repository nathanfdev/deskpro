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
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\TicketLog;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\ORM\StateChange\ChangeCollection;
use Application\DeskPRO\ORM\StateChange\ChangeData;
use Application\DeskPRO\ORM\StateChange\ChangeInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\Util;

class TicketLogGenerator
{
    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    private $ticket;

    /**
     * @var ExecutorContextInterface
     */
    private $context;

    /**
     * @var \Application\DeskPRO\Tickets\StateChangeRecorder
     */
    private $state;

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function __construct(Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->ticket  = $ticket;
        $this->state   = $ticket->getStateChangeRecorder();
        $this->context = $context;
    }


    /**
     * @return \Application\DeskPRO\Entity\TicketLog[]
     */
    public function getLogEntries()
    {
        $group = new TicketLog();
        $group->ticket      = $this->ticket;
        $group->person      = $this->context->getPersonContext();
        $group->action_type = 'action_starter';
        $group->details     = array(
            'event'           => $this->context->getEventType(),
            'event_method'    => $this->context->getEventMethod(),
            'event_performer' => $this->context->getEventPerformer(),
            'person_id'       => $this->context->getPersonContext() ? $this->context->getPersonContext()->id : null,
            'person_name'     => $this->context->getPersonContext() ? $this->context->getPersonContext()->getDisplayName() : null,
            'person_email'    => $this->context->getPersonContext() ? $this->context->getPersonContext()->getPrimaryEmailAddress() : null,
        );

        $logs = array();
        $logs[] = $group;

        if ($this->state->isNewTicket() || $this->context->getEventType() == 'newticket') {
			$data = array(
                'action_type' => 'ticket_created',
                'id_after'    => $this->ticket->id,
                'ticket_id'   => $this->ticket->id,
				'event_performer' => $this->context->getEventPerformer(),
				'event_method'    => $this->context->getEventMethod(),
			);
			if ($this->context->getEventMethod() == 'email' && $this->context->getEmailContext() && $this->context->getEmailContext()->getDeliveredAddresses()) {
				$data['email_to']   = array_map(function($a) { return $a->email; }, $this->context->getEmailContext()->getReceivedAddresses());
				$data['email_from'] = Util::flatMap($this->context->getEmailContext()->getRealFromAddress(), function($v) { return $v->email; });
			}
			$log = $this->getLogFromData($data);
            $log->parent = $group;
            $logs[] = $log;
        }

        foreach ($this->state->getChanges() as $change) {
            $log_data = $this->getLogDataForChange($change);
            if (!$log_data) {
                $this->context->getLogger()->info(sprintf("[TicketLogGenerator] %s -> no data", $change->getField()));
                continue;
            }

            if (isset($log_data[0]) && is_array($log_data[0])) {
                $log_data_set = $log_data;
            } else {
                $log_data_set = array($log_data);
            }

            $log_metadata = $this->state->getMetaDataForChange($change);

            foreach ($log_data_set as $log_data) {
                $log = $this->getLogFromData($log_data);
                if (!$log) {
                    continue;
                }
                if (!$log->action_type) {
                    $log->action_type = $change->getField();
                }

                if ($log_metadata) {
                    if (!empty($log_metadata['trigger'])) {
                        $log->trigger_id = $log_metadata['trigger']->id;
                        $log->setDetailItem('trigger_title', $log_metadata['trigger']->title);
                    }
                    if (!empty($log_metadata['escalation'])) {
                        $log->escalation_id = $log_metadata['escalation']->id;
                        $log->setDetailItem('escalation_title', $log_metadata['escalation']->title);
                    }
                    if (!empty($log_metadata['sla'])) {
                        $log->sla = $log_metadata['sla'];
                        $log->sla_status = $log_metadata['sla_status'];
                        $log->setDetailItem('sla_title', $log_metadata['sla']->title);
                    }
                }

                $this->context->getLogger()->info(sprintf("[TicketLogGenerator] %s -> %s", $change->getField(), $log->action_type));

                $log->parent = $group;

                $logs[] = $log;
            }
        }

        if (count($logs) < 2) {
            return array();
        }

        return $logs;
    }


    /**
     * @param  array     $log_data
     * @return TicketLog
     */
    private function getLogFromData(array $log_data)
    {
        $log = new TicketLog();
        $log->ticket = $this->ticket;
        $log->person = $this->context->getPersonContext();

        if (!empty($log_data['action_type'])) {
            $log->action_type = $log_data['action_type'];
            unset($log_data['action_type']);
        } else {
            return null;
        }

        foreach (array('id_object', 'id_before', 'id_after') as $prop) {
            if (!empty($log_data[$prop])) {
                $log->$prop = $log_data[$prop];
                unset($log_data[$prop]);
            }
        }

        $log->details = $log_data;

        return $log;
    }


    /**
     * @param  ChangeInterface $change
     * @return array
     */
    private function getLogDataForChange(ChangeInterface $change)
    {
        $old = $change->getOld();
        $new = $change->getNew();

        if ($change instanceof ChangeCollection) {
            $added   = $change->getAddedElements();
            $removed = $change->getRemovedElements();
        }

        switch ($change->getField()) {
            case 'agent':
                return array(
                    'action_type' => 'changed_agent',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_agent_id'    => $old ? $old->id : null,
                    'old_agent_name'  => $old ? $old->display_name : null,
                    'old_agent_email' => $old ? $old->primary_email_address : null,
                    'new_agent_id'    => $new ? $new->id : null,
                    'new_agent_name'  => $new ? $new->display_name : null,
                    'new_agent_email' => $new ? $new->primary_email_address : null,
                );
                break;

            case 'agent_team':
                return array(
                    'action_type' => 'changed_agent_team',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_agent_team_id'    => $old ? $old->id : null,
                    'old_agent_team_name'  => $old ? $old->name : null,
                    'new_agent_team_id'    => $new ? $new->id : null,
                    'new_agent_team_name'  => $new ? $new->name : null,
                );
                break;

            case 'category':
                return array(
                    'action_type' => 'changed_category',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_category_id'    => $old ? $old->id : null,
                    'old_category_title' => $old ? $old->title : null,
                    'new_category_id'    => $new ? $new->id : null,
                    'new_category_title' => $new ? $new->title : null,
                );
                break;

            case 'created':
                return array(
                    'action_type' => 'ticket_created',
                    'id_after'    => $this->ticket->id,

                    'ticket_id' => $this->ticket->id,
                );
                break;

            case 'custom_field':
                return array(
                    'action_type'  => 'changed_custom_field',
                    'value_before' => $old ? $old['value'] : null,
                    'value_after'  => $new ? $new['value'] : null,

                    'field_id'   => $old ? $old['field_def']->id : null,
                    'field_name' => $old ? $old['field_def']->title : null
                );
                break;

            case 'department':
                return array(
                    'action_type' => 'changed_department',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_department_id'    => $old ? $old->id : null,
                    'old_department_title' => $old ? $old->getFullTitle() : null,
                    'new_department_id'    => $new ? $new->id : null,
                    'new_department_title' => $new ? $new->getFullTitle() : null,
                );
                break;

            case 'free':
                if ($change instanceof ChangeData) {
                    $data = $change->getData();
                    $data['action_type'] = 'free';
                } else {
                    $data = array(
                        'action_type' => 'free',
                    );
                }

                return $data;
                break;

            case 'hidden_status':
                return array(
                    'action_type' => 'changed_hidden_status',
                    'old_status'  => $old ? $old : null,
                    'new_status'  => $new ? $new : null,
                );
                break;

            case 'hold':
                return array(
                    'id_before' => $old ? 1 : 0,
                    'id_after'  => $new ? 0 : 1,

                    'was_hold'  => (bool)$old,
                    'is_hold'   => (bool)$new,
                );
                break;

            case 'labels':
                return array(
                    'action_type' => 'changed_labels',
                    'added'   => array_map(function ($l) { return $l->label; }, $added),
                    'removed' => array_map(function ($l) { return $l->label; }, $removed)
                );
                break;

            case 'language':
                return array(
                    'action_type' => 'changed_language',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_language_id'    => $old ? $old->id : null,
                    'old_language_title' => $old ? $old->title : null,
                    'new_language_id'    => $new ? $new->id : null,
                    'new_language_title' => $new ? $new->title : null,
                );
                break;

            case 'merge':

                break;

            case 'message':
                $log_set = array();

                if ($new) {
                    $m = $new;
                    $log_data = array();
                    $log_data['action_type']      = 'message_created';
                    $log_data['id_after']         = $m->id;
                    $log_data['message_id']       = $m->id;
                    $log_data['creation_system']  = $m->creation_system;
                    $log_data['is_agent_note']    = $m->is_agent_note;
                    $log_data['is_agent_message'] = $m->person->is_agent;
                    $log_data['ip_address']       = $m->ip_address ?: null;
                    $log_data['email']            = $m->email ?: null;
                    $log_set[] = $log_data;
                }

                if ($old) {
                    $m = $old;
                    $log_data = array();
                    $log_data['action_type']      = 'message_removed';
                    $log_data['id_before']        = $m->id;
                    $log_data['message_id']       = $m->id;
                    $log_data['person_id']        = $m->person->id;
                    $log_data['person_name']      = $m->person->display_name;
                    $log_data['is_agent_note']    = $m->is_agent_note;
                    $log_data['is_agent_message'] = $m->person->is_agent;
                    $log_data['old_message']      = $m->getMessageHtml();
                    $log_set[] = $log_data;
                }

                return $log_set;
                break;

            case 'organization':
                return array(
                    'action_type' => 'changed_organization',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_organization_id'    => $old ? $old->id : null,
                    'old_organization_name'  => $old ? $old->name : null,
                    'new_organization_id'    => $new ? $new->id : null,
                    'new_organization_name'  => $new ? $new->name : null,
                );
                break;

            case 'participants':
                $added_users    = array_filter($added, function ($part) { return !$part->person->is_agent; });
                $added_agents   = array_filter($added, function ($part) { return $part->person->is_agent; });

                $removed_users  = array_filter($removed, function ($part) { return !$part->person->is_agent; });
                $removed_agents = array_filter($removed, function ($part) { return $part->person->is_agent; });

                if ($added_users || $removed_users) {
                    return array(
                        'action_type' => 'changed_user_participants',
                        'added'   => array_map(function ($part) { $p = $part->person; return array('id' => $p->id, 'name' => $p->display_name, 'email' => $p->email_address); }, $added_users),
                        'removed' => array_map(function ($part) { $p = $part->person; return array('id' => $p->id, 'name' => $p->display_name, 'email' => $p->email_address); }, $removed_users)
                    );
                }
                if ($added_agents || $removed_agents) {
                    return array(
                        'action_type' => 'changed_agent_participants',
                        'added'   => array_map(function ($part) { $p = $part->person; return array('id' => $p->id, 'name' => $p->display_name, 'email' => $p->email_address); }, $added_agents),
                        'removed' => array_map(function ($part) { $p = $part->person; return array('id' => $p->id, 'name' => $p->display_name, 'email' => $p->email_address); }, $removed_agents)
                    );
                }
                break;

            case 'person':
                return array(
                    'action_type' => 'changed_person',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_person_id'    => $old ? $old->id : null,
                    'old_person_name'  => $old ? $old->display_name : null,
                    'old_person_email' => $old ? $old->primary_email_address : null,
                    'new_person_id'    => $new ? $new->id : null,
                    'new_person_name'  => $new ? $new->display_name : null,
                    'new_person_email' => $new ? $new->primary_email_address : null,
                );
                break;

            case 'priority':
                return array(
                    'action_type' => 'changed_priority',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_priority_id'    => $old ? $old->id : null,
                    'old_priority_title' => $old ? $old->title : null,
                    'old_priority_pri'   => $old ? $old->priority : null,
                    'new_priority_id'    => $new ? $new->id : null,
                    'new_priority_title' => $new ? $new->title : null,
                    'new_priority_pri'   => $new ? $new->priority : null,
                );
                break;

            case 'product':
                return array(
                    'action_type' => 'changed_product',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_product_id'    => $old ? $old->id : null,
                    'old_product_title' => $old ? $old->title : null,
                    'new_product_id'    => $new ? $new->id : null,
                    'new_product_title' => $new ? $new->title : null,
                );
                break;

            case 'split':

                break;

            case 'status':
                return array(
                    'action_type' => 'changed_status',
                    'id_before' => Ticket::getStatusInt($old) ?: null,
                    'id_after'  => Ticket::getStatusInt($new) ?: null,

                    'old_status' => $old,
                    'new_status' => $new,
                );
                break;

            case 'subject':
                return array(
                    'action_type' => 'changed_subject',
                    'old_subject' => $old,
                    'new_subject' => $new,
                );
                break;

            case 'ticket_slas':
                return array(
                    'action_type' => 'changed_slas',
                    'added'   => array_map(function ($ts) { return array('id' => $ts->sla->id, 'title' => $ts->sla->title); }, $added),
                    'removed' => array_map(function ($ts) { return array('id' => $ts->sla->id, 'title' => $ts->sla->title); }, $removed)
                );
                break;

            case 'urgency':
                return array(
                    'action_type' => 'changed_urgency',
                    'id_before' => $old ?: null,
                    'id_after'  => $new ?: null,

                    'old_urgency' => $old ?: 0,
                    'new_urgency' => $new ?: 0,
                );
                break;

            case 'workflow':
                return array(
                    'action_type' => 'changed_workflow',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_workflow_id'    => $old ? $old->id : null,
                    'old_workflow_title' => $old ? $old->title : null,
                    'new_workflow_id'    => $new ? $new->id : null,
                    'new_workflow_title' => $new ? $new->title : null,
                );
                break;

            case 'trigger':
                return array(
                    'action_type' => 'trigger',
                    'id_after'    => $new['trigger_id'],

                    'trigger_id'    => $new['trigger_id'],
                    'trigger_title' => $new['trigger_title'],
                );
                break;

            case 'ticket_email':
                return array(
                    'action_type' => 'ticket_email',

                    'user_mode'  => $new['user_mode'],
                    'to_name'    => $new['to_name'],
                    'to_email'   => $new['to_email'],
                    'cc_emails'  => $new['cc_emails'],
                    'from_name'  => $new['from_name'],
                    'from_email' => $new['from_email'],
                    'template'   => $new['template'],
                );

            case 'split_to':
                return array(
                    'action_type' => 'split_to',
                    'id_after'    => $new['new_ticket_id'],

                    'message_ids' => $new['message_ids'],
                );

            case 'split_from':
                return array(
                    'action_type' => 'split_from',
                    'id_before'   => $new['old_ticket_id'],

                    'message_ids' => $new['message_ids'],
                );

            case 'merged_from':
                return array(
                    'action_type' => 'merged_from',
                    'id_before'   => $new['old_ticket_id'],
                    'lost_data'   => $new['lost_data']
                );

            case 'app_message':
                return array(
                    'action_type'    => 'app_message',
                    'app_id'         => $new['app_id'],
                    'app_title'      => $new['app_title'],
                    'package_name'   => $new['package_name'],
                    'package_title'  => $new['package_title'],
                    'message'        => $new['message']
                );

            case 'attachments':
                $log_set = array();

                if ($new && isset($new->blob) && !$new->is_inline) {
                    $blob = $new->blob;
                    $log_data = array();
                    $log_data['action_type']     = 'attach_added';
                    $log_data['id_after']        = $new->id;
                    $log_data['attach_id']       = $new->id;
                    $log_data['blob_id']         = $blob->id;
                    $log_data['filename']        = $blob->filename;
                    $log_data['filesize']        = $blob->filesize;
                    $log_data['content_type']    = $blob->content_type;
                    $log_set[] = $log_data;
                }

                if ($old && isset($old->blob) && !$old->is_inline) {
                    $blob = $old->blob;
                    $log_data = array();
                    $log_data['action_type']     = 'attach_removed';
                    $log_data['id_before']       = $old->id;
                    $log_data['attach_id']       = $old->id;
                    $log_data['blob_id']         = $blob->id;
                    $log_data['filename']        = $blob->filename;
                    $log_data['filesize']        = $blob->filesize;
                    $log_data['content_type']    = $blob->content_type;
                    $log_set[] = $log_data;
                }

                return $log_set;

            case 'feedback_rating':
                $log_data = array();
                $log_data['action_type'] = 'feedback_rating';
                $log_data['id_before']   = $old;
                $log_data['id_after']    = $new;

                switch ($new) {
                    case -1: $log_data['rating'] = 'negative'; break;
                    case 0:  $log_data['rating'] = 'neutral';  break;
                    case 1:  $log_data['rating'] = 'positive'; break;
                }

                return $log_data;

            case 'person_email':
                $log_data = array();
                $log_data['action_type'] = 'person_email_changed';
                $log_data['id_before']   = $old ? $old->id : null;
                $log_data['id_after']    = $new ? $new->id : null;

                if ($old) {
                    $log_data['old_email'] = $old->email;
                }
                if ($new) {
                    $log_data['new_email'] = $new->email;
                }

                return $log_data;

            case 'ticket_sla_status':
                $log_data = array();
                $log_data['action_type'] = 'ticket_sla_status';
                $log_data['sla_id']      = $old['sla']->id;
                $log_data['sla_title']   = $old['sla']->title;
                $log_data['old_status']  = $old['status'];
                $log_data['new_status']  = $new['status'];

                return $log_data;

            case 'webhook':
                $data = $change instanceof ChangeData ? $change->getData() : array();
                $data['action_type'] = 'webhook';

                return $data;

            default:
                return array();
        }
    }
}
