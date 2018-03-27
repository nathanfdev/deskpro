<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\Entity\LabelOrganization;
use Application\DeskPRO\Entity\LabelPerson;
use Application\DeskPRO\Entity\LabelTicket;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketSla;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\ORM\StateChange\StateChangeRecorder as BaseStateChangeRecorder;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\CustomData;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\OrgModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\PersonModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketSlaModel;
use DeskPRO\Component\Util\ListUtils;

class StateChangeRecorder extends BaseStateChangeRecorder
{
    /**
     * @var array
     */
    private static $trivial_fields = [
        'access_codes'            => true,
        'ticket_hash'             => true,
        'date_feedback_rating'    => true,
        'date_created'            => true,
        'date_resolved'           => true,
        'date_archived'           => true,
        'date_first_agent_assign' => true,
        'date_first_agent_reply'  => true,
        'date_last_agent_reply'   => true,
        'date_last_user_reply'    => true,
        'date_agent_waiting'      => true,
        'date_user_waiting'       => true,
        'date_status'             => true,
        'total_user_waiting'      => true,
        'total_to_first_reply'    => true,
        'locked_by_agent'         => true,
        'date_locked'             => true,
        'has_attachments'         => true,
        'count_agent_replies'     => true,
        'count_user_replies'      => true,
    ];

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    private $ticket;

    /**
     * @var TicketModel
     */
    private $before_ticket_model;

    /**
     * @var bool
     */
    private $no_id = false;

    /**
     * If this is a trivial changeset.
     *
     * @var bool
     */
    private $is_trivial = false;

    /**
     * When the last trivial check was made.
     *
     * @var null
     */
    private $is_trivial_checkid = null;

    /**
     * @param Ticket $ticket
     */
    public function __construct(Ticket $ticket)
    {
        parent::__construct();

        $this->ticket = $ticket;
        if (!$ticket->id) {
            $this->no_id = true;
        }
    }

    public function touchField($field_id)
    {
        // touchField is called in Ticket model before the change is actually saved,
        // so its a good time to make the 'before' model

        if (!$this->before_ticket_model && !isset(self::$trivial_fields[$field_id])) {
            $this->before_ticket_model = $this->getSimpleTicketModel();
        }

        parent::touchField($field_id);
    }

    /**
     * Get an array of [before, after] simple ticket models. 0th is old, 1st is new.
     *
     * @return TicketModel[]
     */
    public function getBeforeAfterModels()
    {
        return [$this->before_ticket_model, $this->getSimpleTicketModel()];
    }

    private function makePersonModel(Person $person)
    {
        $model           = new PersonModel();
        $model->id       = $person->getId();
        $model->language = $person->getLanguageId();
        $model->labels   = ListUtils::map($person->getLabels(), function (LabelPerson $l) {
            return $l->getLabel();
        });
        $model->user_groups   = $person->getUsergroupIds();
        $model->custom_fields = $this->makeCustomFieldsModels($person->getCustomData());

        return $model;
    }

    private function makeOrgModel(Organization $org)
    {
        $model              = new OrgModel();
        $model->id          = $org->getId();
        $model->user_groups = ListUtils::map($org->getUsergroups(), function (Usergroup $ug) {
            return $ug->getId();
        });
        $model->labels = ListUtils::map($org->getLabels(), function (LabelOrganization $l) {
            return $l->getLabel();
        });
        $model->custom_fields = $this->makeCustomFieldsModels($org->getCustomData());

        return $model;
    }

    private function makeCustomFieldsModels($customData)
    {
        $models = [];
        foreach ($customData as $d) {
            $fid = $d->getFieldId();
            if (isset($models[$fid])) {
                $m = $models[$fid];
            } else {
                $m        = new CustomData();
                $m->field = $fid;
            }
            if ($d->getField()->isMulti()) {
                $m->data[] = $d->getData();
            } else {
                $m->data = $d->getData();
            }

            $models[$fid] = $m;
        }

        return $models;
    }

    /**
     * @return TicketModel
     */
    public function getSimpleTicketModel()
    {
        $cur             = new TicketModel();
        $cur->id         = $this->ticket->getId();
        $cur->agent      = $this->ticket->agent ? $this->ticket->agent->getId() : 0;
        $cur->agent_team = $this->ticket->agent_team ? $this->ticket->agent_team->getId() : 0;
        $cur->department = $this->ticket->department ? $this->ticket->department->getId() : 0;
        $cur->urgency    = $this->ticket->status === Ticket::STATUS_AWAITING_AGENT ? $this->ticket->urgency : 0;
        $cur->status     = $this->ticket->getStatusCode();
        $cur->is_hold    = $this->ticket->is_hold;
        $cur->person     = $this->ticket->person ? $this->makePersonModel($this->ticket->person) : null;
        $cur->labels     = ListUtils::map($this->ticket->labels, function (LabelTicket $l) {
            return $l->getLabel();
        });
        $cur->language              = $this->ticket->language ? $this->ticket->language->getId() : 0;
        $cur->workflow              = $this->ticket->workflow ? $this->ticket->workflow->getId() : 0;
        $cur->priority              = $this->ticket->priority ? $this->ticket->priority->getId() : 0;
        $cur->category              = $this->ticket->category ? $this->ticket->category->getId() : 0;
        $cur->product               = $this->ticket->product ? $this->ticket->product->getId() : 0;
        $cur->organization          = $this->ticket->organization ? $this->makeOrgModel($this->ticket->organization) : null;
        $cur->email_account         = $this->ticket->email_account ? $this->ticket->email_account->getId() : 0;
        $cur->date_user_waiting     = $this->ticket->date_user_waiting;
        $cur->date_agent_waiting    = $this->ticket->date_agent_waiting;
        $cur->date_last_user_reply  = $this->ticket->date_last_user_reply;
        $cur->date_last_agent_reply = $this->ticket->date_last_agent_reply;
        $cur->date_created          = $this->ticket->date_created;
        $cur->followers             = ListUtils::map($this->ticket->getAgentParticipants(), function (Person $a) {
            return $a->getId();
        });
        $cur->slas = ListUtils::map($this->ticket->ticket_slas, function (TicketSla $sla) {
            $slaM = new TicketSlaModel();
            $slaM->sla_id = $sla->sla->getId();
            $slaM->status = $sla->sla_status;
            $slaM->fail_date = $sla->fail_date;
            $slaM->warn_date = $sla->warn_date;
            $slaM->is_completed = $sla->is_completed;

            return $slaM;
        });

        $cur->custom_fields = $this->makeCustomFieldsModels($this->ticket->getCustomData());

        return $cur;
    }

    /**
     * @return bool
     */
    public function isTrivialChangeSet()
    {
        if ($this->is_trivial_checkid === null || $this->is_trivial_checkid < $this->getStateVersion()) {
            $this->is_trivial         = true;
            $this->is_trivial_checkid = $this->getStateVersion();

            foreach ($this->getChangedFields() as $f) {
                if (!isset(self::$trivial_fields[$f])) {
                    $this->is_trivial = false;
                    break;
                }
            }
        }

        return $this->is_trivial;
    }

    /**
     * @return bool
     */
    public function isNewTicket()
    {
        // If the ticket is not a proxy object it means it was created now.
        // - If there was no ID at the time this state recorder was created,
        // it means its part of the same state transaction. (eg state recorder wasnt reset)
        if ($this->no_id && get_class($this->ticket) === 'Application\\DeskPRO\\Entity\\Ticket') {
            return true;
        }

        return false;
    }

    /**
     * Check if there has been a new reply of type.
     *
     * @param string $type
     *
     * @return bool
     */
    private function hasNewMessageOfType($type)
    {
        if (!$this->hasChangedField('message')) {
            return false;
        }

        foreach (array_reverse($this->getChangesForField('message')) as $change) {
            $message = $change->getNew();
            if (!$message) {
                continue;
            }

            switch ($type) {
                case 'agent_reply':
                    if (!$message->is_agent_note && $message->person->is_agent) {
                        return true;
                    }
                    break;
                case 'agent_note':
                    if ($message->is_agent_note) {
                        return true;
                    }
                    break;
                case 'user_reply':
                    if (!$message->is_agent_note && !$message->person->is_agent) {
                        return true;
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Get new messages of type.
     *
     * @param string $type
     *
     * @return bool
     */
    private function getNewMessagesOfType($type = 'any')
    {
        if (!$this->hasChangedField('message')) {
            return [];
        }

        $messages = [];

        foreach (array_reverse($this->getChangesForField('message')) as $change) {
            $message = $change->getNew();
            if (!$message) {
                continue;
            }

            switch ($type) {
                case 'any':
                    $messages[] = $message;
                    break;

                case 'agent_reply':
                    if (!$message->is_agent_note && $message->person->is_agent) {
                        $messages[] = $message;
                    }
                    break;
                case 'agent_note':
                    if ($message->is_agent_note) {
                        $messages[] = $message;
                    }
                    break;
                case 'user_reply':
                    if (!$message->is_agent_note && !$message->person->is_agent) {
                        $messages[] = $message;
                    }
                    break;
            }
        }

        return $messages;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->hasChangedField('status') && $this->ticket->isDeleted();
    }

    /**
     * Has there been a new agent reply?
     *
     * @return bool
     */
    public function hasNewReply()
    {
        return $this->hasChangedField('message');
    }

    /**
     * Has there been a new agent reply?
     *
     * @return bool
     */
    public function hasNewAgentReply()
    {
        return $this->hasNewMessageOfType('agent_reply');
    }

    /**
     * Has there been a new agent note?
     *
     * @return bool
     */
    public function hasNewAgentNote()
    {
        return $this->hasNewMessageOfType('agent_note');
    }

    /**
     * Has there been a new user reply?
     *
     * @return bool
     */
    public function hasNewUserReply()
    {
        return $this->hasNewMessageOfType('user_reply');
    }

    /**
     * Get an array of any new repies.
     *
     * @return \Application\DeskPRO\Entity\TicketMessage[]
     */
    public function getNewReplies()
    {
        return $this->getNewMessagesOfType('any');
    }

    /**
     * Get an array of any new agent replies.
     *
     * @return \Application\DeskPRO\Entity\TicketMessage[]
     */
    public function getNewAgentReplies()
    {
        return $this->getNewMessagesOfType('agent_reply');
    }

    /**
     * Get an array of any new agent notes.
     *
     * @return \Application\DeskPRO\Entity\TicketMessage[]
     */
    public function getNewAgentNotes()
    {
        return $this->getNewMessagesOfType('agent_note');
    }

    /**
     * Get an array of any new user replies.
     *
     * @return \Application\DeskPRO\Entity\TicketMessage[]
     */
    public function getNewUserReplies()
    {
        return $this->getNewMessagesOfType('user_reply');
    }
}
