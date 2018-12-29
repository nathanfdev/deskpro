<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\TicketLog;

use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Problem;
use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\ORM\StateChange\ChangeCollection;
use Application\DeskPRO\ORM\StateChange\ChangeData;
use Application\DeskPRO\ORM\StateChange\ChangeInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Entity\TicketFollowUp;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use Orb\Util\Util;

/**
 * Class TicketLogGenerator.
 */
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
        $group              = new TicketLog();
        $group->ticket      = $this->ticket;
        $group->person      = $this->context->getPersonContext();
        $group->action_type = 'action_starter';

        $details = [
            'event'           => $this->context->getEventType(),
            'event_method'    => $this->context->getEventMethod(),
            'event_performer' => $this->context->getEventPerformer(),
            'person_id'       => $this->context->getPersonContext() ? $this->context->getPersonContext()->id : null,
            'person_name'     => $this->context->getPersonContext() ? $this->context->getPersonContext()->getDisplayName() : null,
            'person_email'    => $this->context->getPersonContext() ? $this->context->getPersonContext()->getPrimaryEmailAddress() : null,
        ];

        $apiKey = $this->context->getVars()->get('via_api_key');
        if ($apiKey) {
            $details['via_api_key'] = $apiKey;
        }
        $followUpId = $this->context->getVars()->get('followup_id');
        if ($followUpId) {
            $details['followup_id'] = $followUpId;
        }

        $group->setDetails($details);

        $logs   = [];
        $logs[] = $group;

        if ($this->state->isNewTicket() || $this->context->getEventType() == 'newticket') {
            $data = [
                'action_type'     => 'ticket_created',
                'id_after'        => $this->ticket->id,
                'ticket_id'       => $this->ticket->id,
                'event_performer' => $this->context->getEventPerformer(),
                'event_method'    => $this->context->getEventMethod(),
            ];
            if ($this->context->getEventMethod() == 'email' && $this->context->getEmailContext() && $this->context->getEmailContext()->getDeliveredAddresses()) {
                $data['email_to'] = array_map(function ($a) {
                    return $a->email;
                }, $this->context->getEmailContext()->getReceivedAddresses());
                $data['email_from'] = Util::flatMap($this->context->getEmailContext()->getRealFromAddress(), function ($v) {
                    return $v->email;
                });
            }
            $log         = $this->getLogFromData($data);
            $log->parent = $group;
            $logs[]      = $log;
        }

        foreach ($this->state->getChanges() as $change) {
            $log_data = $this->getLogDataForChange($change);
            if (!$log_data) {
                $this->context->getLogger()->info(sprintf('[TicketLogGenerator] %s -> no data', $change->getField()));
                continue;
            }

            if (isset($log_data[0]) && is_array($log_data[0])) {
                $log_data_set = $log_data;
            } else {
                $log_data_set = [$log_data];
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
                        $log->setDetailItem('trigger_event', $log_metadata['trigger']->event_trigger);
                    }
                    if (!empty($log_metadata['escalation'])) {
                        $log->escalation_id = $log_metadata['escalation']->id;
                        $log->setDetailItem('escalation_title', $log_metadata['escalation']->title);
                    }
                    if (!empty($log_metadata['sla'])) {
                        $log->sla        = $log_metadata['sla'];
                        $log->sla_status = $log_metadata['sla_status'];
                        $log->setDetailItem('sla_title', $log_metadata['sla']->title);
                    }
                }

                $this->context->getLogger()->info(sprintf('[TicketLogGenerator] %s -> %s', $change->getField(), $log->action_type));

                $log->parent = $group;

                $logs[] = $log;
            }
        }

        if (count($logs) < 2) {
            return [];
        }

        return $logs;
    }

    /**
     * @param array $log_data
     *
     * @return TicketLog
     */
    private function getLogFromData(array $log_data)
    {
        $log         = new TicketLog();
        $log->ticket = $this->ticket;
        $log->person = $this->context->getPersonContext();

        if (!empty($log_data['action_type'])) {
            $log->action_type = $log_data['action_type'];
            unset($log_data['action_type']);
        } else {
            return;
        }

        foreach (['id_object', 'id_before', 'id_after'] as $prop) {
            if (!empty($log_data[$prop])) {
                $log->$prop = $log_data[$prop];
                unset($log_data[$prop]);
            }
        }

        $log->details = $log_data;

        return $log;
    }

    /**
     * @param ChangeInterface $change
     *
     * @return array
     */
    public function getLogDataForChange(ChangeInterface $change)
    {
        $old = $change->getOld();
        $new = $change->getNew();

        if ($change instanceof ChangeCollection) {
            $added   = $change->getAddedElements();
            $removed = $change->getRemovedElements();
        }

        switch ($change->getField()) {
            case 'agent':
                return [
                    'action_type' => 'changed_agent',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_agent_id'    => $old ? $old->id : null,
                    'old_agent_name'  => $old ? $old->display_name : null,
                    'old_agent_email' => $old ? $old->primary_email_address : null,
                    'new_agent_id'    => $new ? $new->id : null,
                    'new_agent_name'  => $new ? $new->display_name : null,
                    'new_agent_email' => $new ? $new->primary_email_address : null,
                ];
                break;

            case 'agent_team':
                return [
                    'action_type' => 'changed_agent_team',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_agent_team_id'   => $old ? $old->id : null,
                    'old_agent_team_name' => $old ? $old->name : null,
                    'new_agent_team_id'   => $new ? $new->id : null,
                    'new_agent_team_name' => $new ? $new->name : null,
                ];
                break;

            case 'category':
                return [
                    'action_type' => 'changed_category',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_category_id'    => $old ? $old->id : null,
                    'old_category_title' => $old ? $old->title : null,
                    'new_category_id'    => $new ? $new->id : null,
                    'new_category_title' => $new ? $new->title : null,
                ];
                break;

            case 'created':
                return [
                    'action_type' => 'ticket_created',
                    'id_after'    => $this->ticket->id,

                    'ticket_id' => $this->ticket->id,
                ];
                break;

            case 'custom_field':
            case 'custom_data':
                if (empty($old['value']) && empty($new['value'])) {
                    return [];
                }

                return [
                    'action_type'  => 'changed_custom_field',
                    'value_before' => !empty($old['value']) ? $old['value'] : null,
                    'value_after'  => !empty($new['value']) ? $new['value'] : null,

                    'field_id'   => !empty($old['field_def']) ? $old['field_def']->id : null,
                    'field_name' => !empty($old['field_def']) ? $old['field_def']->title : null,
                ];
                break;

            case 'brand':
                return [
                    'action_type' => 'changed_brand',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_brand_id'   => $old ? $old->id : null,
                    'old_brand_name' => $old ? $old->getName() : null,
                    'new_brand_id'   => $new ? $new->id : null,
                    'new_brand_name' => $new ? $new->getName() : null,
                ];
                break;

            case 'department':
                return [
                    'action_type' => 'changed_department',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_department_id'    => $old ? $old->id : null,
                    'old_department_title' => $old ? $old->getFullTitle() : null,
                    'new_department_id'    => $new ? $new->id : null,
                    'new_department_title' => $new ? $new->getFullTitle() : null,
                ];
                break;

            case 'free':
                if ($change instanceof ChangeData) {
                    $data                = $change->getData();
                    $data['action_type'] = 'free';
                } else {
                    $data = [
                        'action_type' => 'free',
                    ];
                }

                return $data;
                break;

            case 'labels':
                return [
                    'action_type' => 'changed_labels',
                    'added'       => array_map(function ($l) {
                        return $l->label;
                    }, $added),
                    'removed' => array_map(function ($l) {
                        return $l->label;
                    }, $removed),
                ];
                break;

            case 'language':
                return [
                    'action_type' => 'changed_language',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_language_id'    => $old ? $old->id : null,
                    'old_language_title' => $old ? $old->title : null,
                    'new_language_id'    => $new ? $new->id : null,
                    'new_language_title' => $new ? $new->title : null,
                ];
                break;

            case 'merge':

                break;

            case 'message':
                $logSet = [];

                if ($new) {
                    $m                           = $new;
                    $logData                     = [];
                    $logData['action_type']      = 'message_created';
                    $logData['id_after']         = $m->id;
                    $logData['message_id']       = $m->id;
                    $logData['creation_system']  = $m->creation_system;
                    $logData['is_agent_note']    = $m->is_agent_note;
                    $logData['is_agent_message'] = $m->person->is_agent;
                    $logData['ip_address']       = $m->ip_address ?: null;
                    $logData['email']            = $m->email ?: null;
                    $logSet[]                    = $logData;
                }

                if ($old) {
                    $m                           = $old;
                    $logData                     = [];
                    $logData['action_type']      = 'message_removed';
                    $logData['id_before']        = $m->id;
                    $logData['message_id']       = $m->id;
                    $logData['person_id']        = $m->person->id;
                    $logData['person_name']      = $m->person->display_name;
                    $logData['is_agent_note']    = $m->is_agent_note;
                    $logData['is_agent_message'] = $m->person->is_agent;
                    $logData['old_message']      = $m->getMessageHtml();
                    $logSet[]                    = $logData;
                }

                return $logSet;
                break;

            case 'feedback_link':
                $logSet = [];

                if ($new) {
                    $m                      = $new;
                    $logData                = [];
                    $logData['action_type'] = 'feedback_link_added';
                    $logData['id_after']    = $m->getId();
                    $logData['person_id']   = $m->getPerson() ? $m->getPerson()->getId() : '';
                    $logSet[]               = $logData;
                }

                if ($old) {
                    $m                      = $old;
                    $logData                = [];
                    $logData['action_type'] = 'feedback_link_removed';
                    $logData['id_before']   = $m->getId();
                    $logData['person_id']   = $m->getPerson() ? $m->getPerson()->getId() : '';
                    $logSet[]               = $logData;
                }

                return $logSet;
                break;

            case 'followUp':
                $logSet = [];

                if ($new instanceof TicketFollowUp) {
                    $logData['action_type'] = 'followup_created';
                    $logData['id_after']    = $new->getId();
                    $logData['followup_id'] = $new->getId();
                    $logData['date_to_run'] = $new->getDateToRun();
                    $logSet[]               = $logData;
                }

                if ($old instanceof TicketFollowUp) {
                    $logData['action_type'] = 'followup_removed';
                    $logData['id_before']   = $old->getId();
                    $logData['followup_id'] = $old->getId();
                    $logData['date_to_run'] = $old->getDateToRun();
                    $logSet[]               = $logData;
                }

                return $logSet;
                break;

            case 'organization':
                return [
                    'action_type' => 'changed_organization',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_organization_id'   => $old ? $old->id : null,
                    'old_organization_name' => $old ? $old->name : null,
                    'new_organization_id'   => $new ? $new->id : null,
                    'new_organization_name' => $new ? $new->name : null,
                ];
                break;

            case 'participants':
                $added_users = array_filter($added, function ($part) {
                    return !$part->person->is_agent;
                });
                $added_agents = array_filter($added, function ($part) {
                    return $part->person->is_agent;
                });

                $removed_users = array_filter($removed, function ($part) {
                    return !$part->person->is_agent;
                });
                $removed_agents = array_filter($removed, function ($part) {
                    return $part->person->is_agent;
                });

                if ($added_users || $removed_users) {
                    return [
                        'action_type' => 'changed_user_participants',
                        'added'       => array_map(function ($part) {
                            $p = $part->person;

                            return ['id' => $p->id, 'name' => $p->display_name, 'email' => $p->email_address];
                        }, $added_users),
                        'removed' => array_map(function ($part) {
                            $p = $part->person;

                            return ['id' => $p->id, 'name' => $p->display_name, 'email' => $p->email_address];
                        }, $removed_users),
                    ];
                }
                if ($added_agents || $removed_agents) {
                    return [
                        'action_type' => 'changed_agent_participants',
                        'added'       => array_map(function ($part) {
                            $p = $part->person;

                            return ['id' => $p->id, 'name' => $p->display_name, 'email' => $p->email_address];
                        }, $added_agents),
                        'removed' => array_map(function ($part) {
                            $p = $part->person;

                            return ['id' => $p->id, 'name' => $p->display_name, 'email' => $p->email_address];
                        }, $removed_agents),
                    ];
                }
                break;

            case 'person':
                return [
                    'action_type' => 'changed_person',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_person_id'    => $old ? $old->id : null,
                    'old_person_name'  => $old ? $old->display_name : null,
                    'old_person_email' => $old ? $old->primary_email_address : null,
                    'new_person_id'    => $new ? $new->id : null,
                    'new_person_name'  => $new ? $new->display_name : null,
                    'new_person_email' => $new ? $new->primary_email_address : null,
                ];
                break;

            case 'priority':
                return [
                    'action_type' => 'changed_priority',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_priority_id'    => $old ? $old->id : null,
                    'old_priority_title' => $old ? $old->title : null,
                    'old_priority_pri'   => $old ? $old->priority : null,
                    'new_priority_id'    => $new ? $new->id : null,
                    'new_priority_title' => $new ? $new->title : null,
                    'new_priority_pri'   => $new ? $new->priority : null,
                ];
                break;

            case 'product':
                return [
                    'action_type' => 'changed_product',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_product_id'    => $old ? $old->id : null,
                    'old_product_title' => $old ? $old->title : null,
                    'new_product_id'    => $new ? $new->id : null,
                    'new_product_title' => $new ? $new->title : null,
                ];
                break;

            case 'split':

                break;

            case 'status':
                $logSet = [];

                $logSet[] = [
                    'action_type' => 'changed_status',
                    'id_before'   => Ticket::getStatusInt($old) ?: null,
                    'id_after'    => Ticket::getStatusInt($new) ?: null,

                    'old_status' => $old,
                    'new_status' => $new,
                ];

                if ($new == TicketStatus::STATUS_TYPE_PENDING) {
                    $logSet[] = [
                        'action_type' => 'changed_hold',
                        'id_before'   => 0,
                        'id_after'    => 1,

                        'was_hold' => false,
                        'is_hold'  => true,
                    ];
                }

                if ($old == TicketStatus::STATUS_TYPE_PENDING) {
                    $logSet[] = [
                        'action_type' => 'changed_hold',
                        'id_before'   => 1,
                        'id_after'    => 0,

                        'was_hold' => true,
                        'is_hold'  => false,
                    ];
                }

                return $logSet;
                break;

            case 'ticket_status':
                return [
                    'action_type' => 'changed_ticket_status',
                    'id_before'   => $old ? $old->getId() : null,
                    'id_after'    => $new ? $new->getId() : null,

                    'old_title'       => $old ? $old->getTitle() : null,
                    'new_title'       => $new ? $new->getTitle() : null,
                    'old_status_code' => $old ? $old->getStatusCode() : null,
                    'new_status_code' => $new ? $new->getStatusCode() : null,
                ];
                break;

            case 'subject':
                return [
                    'action_type' => 'changed_subject',
                    'old_subject' => $old,
                    'new_subject' => $new,
                ];
                break;

            case 'ticket_slas':
                return [
                    'action_type' => 'changed_slas',
                    'added'       => array_map(function ($ts) {
                        return ['id' => $ts->sla->id, 'title' => $ts->sla->title];
                    }, $added),
                    'removed' => array_map(function ($ts) {
                        return ['id' => $ts->sla->id, 'title' => $ts->sla->title];
                    }, $removed),
                ];
                break;

            case 'ticket_slas_status':
                if (!empty($new['sla'])) {
                    return [
                        'action_type' => 'changed_sla_status',
                        'sla_id'      => $new['sla']->id,
                        'sla_title'   => $new['sla']->title,
                        'old_status'  => $new['old_status'],
                        'new_status'  => $new['new_status'],
                    ];
                }

                return;

            case 'urgency':
                return [
                    'action_type' => 'changed_urgency',
                    'id_before'   => $old ?: null,
                    'id_after'    => $new ?: null,

                    'old_urgency' => $old ?: 0,
                    'new_urgency' => $new ?: 0,
                ];
                break;

            case 'workflow':
                return [
                    'action_type' => 'changed_workflow',
                    'id_before'   => $old ? $old->id : null,
                    'id_after'    => $new ? $new->id : null,

                    'old_workflow_id'    => $old ? $old->id : null,
                    'old_workflow_title' => $old ? $old->title : null,
                    'new_workflow_id'    => $new ? $new->id : null,
                    'new_workflow_title' => $new ? $new->title : null,
                ];
                break;

            case 'trigger':
                return [
                    'action_type' => 'trigger',
                    'id_after'    => $new['trigger_id'],

                    'trigger_id'    => $new['trigger_id'],
                    'trigger_title' => $new['trigger_title'],
                ];
                break;

            case 'ticket_email':
                return [
                    'action_type' => 'ticket_email',

                    'user_mode'  => $new['user_mode'],
                    'to_name'    => $new['to_name'],
                    'to_email'   => $new['to_email'],
                    'cc_emails'  => $new['cc_emails'],
                    'from_name'  => $new['from_name'],
                    'from_email' => $new['from_email'],
                    'template'   => $new['template'],

                    'sendmail_source_id' => $new['sendmail_source_id'],
                    'id_after'           => $new['sendmail_source_id'],
                ];

            case 'split_to':
                return [
                    'action_type' => 'split_to',
                    'id_after'    => $new['new_ticket_id'],

                    'message_ids' => $new['message_ids'],
                ];

            case 'split_from':
                return [
                    'action_type' => 'split_from',
                    'id_before'   => $new['old_ticket_id'],

                    'message_ids' => $new['message_ids'],
                ];

            case 'merged_from':
                return [
                    'action_type' => 'merged_from',
                    'id_before'   => $new['old_ticket_id'],
                    'lost_data'   => $new['lost_data'],
                ];

            case 'app_message':
                return [
                    'action_type'   => 'app_message',
                    'app_id'        => $new['app_id'],
                    'app_title'     => $new['app_title'],
                    'package_name'  => $new['package_name'],
                    'package_title' => $new['package_title'],
                    'message'       => $new['message'],
                ];

            case 'attachments':
                $logSet = [];

                if ($new && isset($new->blob) && !$new->is_inline) {
                    $blob                    = $new->blob;
                    $logData                 = [];
                    $logData['action_type']  = 'attach_added';
                    $logData['id_after']     = $new->id;
                    $logData['attach_id']    = $new->id;
                    $logData['blob_id']      = $blob->id;
                    $logData['filename']     = $blob->filename;
                    $logData['filesize']     = $blob->filesize;
                    $logData['content_type'] = $blob->content_type;
                    $logSet[]                = $logData;
                }

                if ($old && isset($old->blob) && !$old->is_inline) {
                    $blob                    = $old->blob;
                    $logData                 = [];
                    $logData['action_type']  = 'attach_removed';
                    $logData['id_before']    = $old->id;
                    $logData['attach_id']    = $old->id;
                    $logData['blob_id']      = $blob->id;
                    $logData['filename']     = $blob->filename;
                    $logData['filesize']     = $blob->filesize;
                    $logData['content_type'] = $blob->content_type;
                    $logSet[]                = $logData;
                }

                return $logSet;

            case 'feedback_rating':
                $logData                = [];
                $logData['action_type'] = 'feedback_rating';
                $logData['id_before']   = $old;
                $logData['id_after']    = $new;

                switch ($new) {
                    case -1:
                        $logData['rating'] = 'negative';
                        break;
                    case 0:
                        $logData['rating'] = 'neutral';
                        break;
                    case 1:
                        $logData['rating'] = 'positive';
                        break;
                }

                return $logData;

            case 'person_email':
                $logData                = [];
                $logData['action_type'] = 'person_email_changed';
                $logData['id_before']   = $old ? $old->id : null;
                $logData['id_after']    = $new ? $new->id : null;

                if ($old) {
                    $logData['old_email'] = $old->email;
                }
                if ($new) {
                    $logData['new_email'] = $new->email;
                }

                return $logData;

            case 'ticket_sla_status':
                $logData                = [];
                $logData['action_type'] = 'ticket_sla_status';
                $logData['sla_id']      = $old['sla']->id;
                $logData['sla_title']   = $old['sla']->title;
                $logData['old_status']  = $old['status'];
                $logData['new_status']  = $new['status'];

                return $logData;

            case 'message_note_status':
                $logData                   = [];
                $logData['action_type']    = 'message_note_status';
                $logData['message_id']     = $new['message_id'];
                $logData['was_agent_note'] = !$new['is_agent_note'];
                $logData['is_agent_note']  = $new['is_agent_note'];

                return $logData;

            case 'webhook':
                $data                = $change instanceof ChangeData ? $change->getData() : [];
                $data['action_type'] = 'webhook';

                return $data;

            case 'deleted_attachments':
                $data                = $change instanceof ChangeData ? $change->getData() : [];
                $data['action_type'] = 'deleted_attachments';

                return $data;

            case 'problems':
                return [
                    'action_type' => 'changed_problems',
                    'added'       => array_map(function (Problem $p) {
                        return $p->getTitle();
                    }, $added),
                    'removed' => array_map(function (Problem $p) {
                        return $p->getTitle();
                    }, $removed),
                ];
                break;

            // Custom fields changed
            case strpos($change->getField(), 'custom_data.') === 0:
                $value_before = null;
                $value_after  = null;

                /* @var $field CustomDefTicket */
                if ($old && $old->field) {
                    $field = $old->field;
                } elseif ($new && $new->field) {
                    $field = $new->field;
                }

                if (!$field) {
                    return;
                }

                if ($field->parent) {
                    $field = $field->parent;
                }

                $field_id   = $field->id;
                $field_name = $field->getTitle();
                $is_choice  = $field->isChoiceType();

                if ($old) {
                    $value_before = $old->getData();

                    if ($is_choice) {
                        $value_before = $old->field ? $old->field->getTitle() : null;
                    }
                }
                if ($new) {
                    $value_after = $new->getData();

                    if ($is_choice) {
                        $value_after = $new->field ? $new->field->getTitle() : null;
                    }
                }

                $logData                 = [];
                $logData['action_type']  = 'changed_custom_field';
                $logData['field_name']   = $field_name;
                $logData['field_id']     = $field_id;
                $logData['value_before'] = $value_before;
                $logData['value_after']  = $value_after;
                $logData['is_choice']    = $is_choice;
                $logData['type']         = $field->getType();

                return $logData;

            case 'email_account':
                return [
                    'action_type' => 'email_account',
                    'old'         => $old ? $old->address : null,
                    'new'         => $new ? $new->address : null,
                ];
                break;
            case 'parent_ticket':
                return [
                    'action_type' => 'parent_ticket',
                    'old'         => $old ? $old->id : null,
                    'new'         => $new ? $new->id : null,
                ];
            case 'new_tasks':
                if ($new instanceof Task) {
                    return [
                        'action_type' => 'task_created',
                        'task_id'     => $new->getId(),
                        'task_title'  => $new->getTitle(),
                    ];
                }

                return [];
                break;

            default:
                return [];
        }
    }
}
