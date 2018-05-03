<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity;

use DeskPRO\Component\Util\ListUtils;

class TicketModel
{
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var PersonModel|null
     */
    public $person = null;

    /**
     * @var OrgModel|null
     */
    public $organization = null;

    /**
     * @var string
     */
    public $status = 'awaiting_agent';

    /**
     * @var int
     */
    public $department = 0;

    /**
     * @var int
     */
    public $agent = 0;

    /**
     * @var int
     */
    public $agent_team = 0;

    /**
     * @var int
     */
    public $language = 0;

    /**
     * @var int
     */
    public $email_account = 0;

    /**
     * @var int[]
     */
    public $followers = [];

    /**
     * @var int
     */
    public $product = 0;

    /**
     * @var int
     */
    public $category = 0;

    /**
     * @var int
     */
    public $priority = 0;

    /**
     * @var int
     */
    public $urgency = 0;

    /**
     * @var int
     */
    public $workflow = 0;

    /**
     * @var string[]
     */
    public $labels = [];

    /**
     * @var bool
     */
    public $is_hold = false;

    /**
     * @var \DateTime|null
     */
    public $date_created = null;

    /**
     * @var \DateTime|null
     */
    public $date_last_agent_reply = null;

    /**
     * @var \DateTime|null
     */
    public $date_last_user_reply = null;

    /**
     * @var \DateTime|null
     */
    public $date_agent_waiting = null;

    /**
     * @var \DateTime|null
     */
    public $date_user_waiting = null;

    /**
     * @var int[]
     */
    public $slas = [];

    /**
     * @var TicketSlaModel[]
     */
    public $slasInfo = [];

    /**
     * @var CustomData[]
     */
    public $custom_fields = [];

    /**
     * @var array
     */
    public $new_messages = [];

    public function getChangedFields(TicketModel $other)
    {
        $changed = [];

        foreach ([
            'id', 'status', 'department', 'agent', 'agent_team',
            'language', 'email_account', 'product', 'priority',
            'urgency', 'workflow', 'is_hold', 'date_created',
            'date_last_agent_reply', 'date_last_user_reply',
            'date_agent_waiting', 'date_user_waiting',
        ] as $simpleField) {
            if ($this->$simpleField != $other->$simpleField) {
                $changed[] = "ticket.$simpleField";
            }
        }

        foreach ([
            'followers', 'labels',
        ] as $arrayField) {
            if (!ListUtils::isSame($this->$arrayField, $other->$arrayField)) {
                $changed[] = "ticket.$arrayField";
            }
        }

        // SLAs
        if (!ListUtils::isSame($this->slasInfo, $other->slas)) {
            $changed[] = 'ticket.slas';
        } else {
            foreach ($this->slasInfo as $sla) {
                if ($sla->getChangedFields($other->slasInfo[$sla->id])) {
                    $changed[] = 'ticket.slas';
                    break;
                }
            }
        }

        // Custom fields
        $customFieldChanged = CustomData::compareFieldArrays($this->custom_fields, $other->custom_fields);
        foreach ($customFieldChanged as $fieldId) {
            $changed[] = "ticket.data.$fieldId";
        }

        // Models
        foreach ([
            'person', 'organization',
        ] as $modelField) {
            if ($this->$modelField && $other->$modelField) {
                $modelChanged = $this->$modelField->getChangedFields($other->$modelField);
                $changed      = array_merge($changed, $modelChanged);
            } elseif ($this->$modelField && !$other->$modelField || $other->$modelField && !$this->$modelField) {
                $changed[] = "ticket.$modelField";
            }
        }

        return $changed;
    }
}
