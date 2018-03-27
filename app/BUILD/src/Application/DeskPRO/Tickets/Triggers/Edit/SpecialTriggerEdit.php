<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\Triggers\Edit;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckDepartment;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckEmailAccount;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckSatisfactionSubmittedRating;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermComposite;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;

class SpecialTriggerEdit
{
    const TYPE_DEPARTMENT    = 'Department';
    const TYPE_EMAIL_ACCOUNT = 'EmailAccount';
    const TYPE_SATISFACTION  = 'default_update_satisfaction';

    /**
     * @var string
     */
    private $type;

    /**
     * @var \Application\DeskPRO\Entity\Department|\Application\DeskPRO\Entity\EmailAccount|string
     */
    private $obj;

    /**
     * @var string
     */
    private $event;

    /**
     * @param Department $department
     *
     * @return SpecialTriggerEdit
     */
    public static function createWithDepartment(Department $department, $event)
    {
        return new self(self::TYPE_DEPARTMENT, $department, $event);
    }

    /**
     * @param EmailAccount $account
     *
     * @return SpecialTriggerEdit
     */
    public static function createWithEmailAccount(EmailAccount $account)
    {
        return new self(self::TYPE_EMAIL_ACCOUNT, $account, TicketTrigger::EVENT_TYPE_NEWTICKET);
    }

    public static function createWithSatisfaction($type)
    {
        return new self(self::TYPE_SATISFACTION, $type, TicketTrigger::EVENT_TYPE_UPDATE);
    }

    /**
     * @param string                         $type
     * @param Department|EmailAccount|string $obj
     */
    private function __construct($type, $obj, $event)
    {
        $this->type  = $type;
        $this->obj   = $obj;
        $this->event = $event;
    }

    /**
     * @param TicketTrigger $trigger
     */
    public function applyToTrigger(TicketTrigger $trigger)
    {
        switch ($this->type) {
            case self::TYPE_DEPARTMENT:
                $this->applyDepartmentToTrigger($trigger);
                break;

            case self::TYPE_EMAIL_ACCOUNT:
                $this->applyEmailAccountToTrigger($trigger);
                break;

            case self::TYPE_SATISFACTION:
                $this->applySatisfactionToTrigger($trigger);
                break;
        }
    }

    /**
     * @param TicketTrigger $trigger
     */
    private function applyDepartmentToTrigger(TicketTrigger $trigger)
    {
        $trigger->title         = "Trigger for Department: {$this->obj->title}";
        $trigger->department    = $this->obj;
        $trigger->email_account = null;

        $terms     = new TriggerTerms();
        $terms_set = new TriggerTermComposite();

        if ($this->event == TicketTrigger::EVENT_TYPE_UPDATE) {
            $trigger->event_trigger = 'update';
            $trigger->by_agent_mode = ['api', 'email', 'web', 'mobile'];
            $trigger->by_user_mode  = ['api', 'email', 'form', 'portal', 'widget'];

            $terms_set->add(new CheckDepartment('changed_to', ['department_ids' => [$this->obj->id]]));
        } else {
            $trigger->event_trigger = 'newticket';
            $trigger->by_agent_mode = ['api', 'email', 'web', 'mobile'];
            $trigger->by_user_mode  = ['api', 'form', 'portal', 'widget'];

            $terms_set->add(new CheckDepartment('is', ['department_ids' => [$this->obj->id]]));
        }

        $terms->addTerm($terms_set);
        $trigger->terms = $terms;
    }

    /**
     * @param TicketTrigger $trigger
     */
    private function applyEmailAccountToTrigger(TicketTrigger $trigger)
    {
        $trigger->title         = "Trigger for Email Account: {$this->obj->address}";
        $trigger->email_account = $this->obj;
        $trigger->department    = null;

        $terms     = new TriggerTerms();
        $terms_set = new TriggerTermComposite();
        $terms_set->add(new CheckEmailAccount('is', ['email_account_ids' => [$this->obj->id]]));
        $terms->addTerm($terms_set);

        $trigger->terms = $terms;

        $trigger->event_trigger = 'newticket';
        $trigger->by_agent_mode = ['email'];
        $trigger->by_user_mode  = ['email'];
    }

    private function applySatisfactionToTrigger(TicketTrigger $trigger)
    {
        $type                   = ucfirst($this->obj);
        $trigger->title         = "Trigger for {$type} Feedback";
        $trigger->email_account = null;
        $trigger->department    = null;

        $ratings = [
            'positive' => 1,
            'neutral'  => 0,
            'negative' => -1,
        ];

        $terms     = new TriggerTerms();
        $terms_set = new TriggerTermComposite();
        $terms_set->add(new CheckSatisfactionSubmittedRating('is', ['rating' => $ratings[$this->obj]]));
        $terms->addTerm($terms_set);
        $trigger->terms = $terms;

        $trigger->event_trigger = 'update';
        $trigger->by_agent_mode = ['api', 'email', 'web', 'mobile'];
        $trigger->by_user_mode  = ['api', 'email', 'form', 'portal', 'widget'];

        $trigger->sys_name = self::TYPE_SATISFACTION.'_'.$this->obj;
    }
}
