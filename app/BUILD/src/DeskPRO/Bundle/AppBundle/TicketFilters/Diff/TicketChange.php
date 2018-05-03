<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Diff;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;

class TicketChange
{
    /**
     * @var TicketModel
     */
    private $ticketA;

    /**
     * @var TicketModel
     */
    private $ticketB;

    /**
     * fieldId => true.
     *
     * @var array
     */
    private $changedFieldsMap;

    /**
     * @var array
     */
    private $changedFields;

    /**
     * ChangeSet constructor.
     *
     * @param TicketModel $ticketA
     * @param TicketModel $ticketB
     */
    public function __construct(TicketModel $ticketA, TicketModel $ticketB)
    {
        $this->ticketA = $ticketA;
        $this->ticketB = $ticketB;
    }

    private function initChanged()
    {
        if ($this->changedFields !== null) {
            return;
        }

        $ticketA = $this->ticketA;
        $ticketB = $this->ticketB;

        $changedFields = $ticketA->getChangedFields($ticketB);

        // Virtual fields used when calculating notifs
        if (!$ticketA->id) {
            $changedFields[] = 'ticket.is_new';
        }
        if (!empty($ticketB->new_messages)) {
            $changedFields[] = 'ticket.new_messages';
        }

        $this->changedFields    = $changedFields;
        $this->changedFieldsMap = array_fill_keys($changedFields, true);
    }

    /**
     * Get changed fields between a and b.
     */
    public function getChangedFields()
    {
        $this->initChanged();

        return $this->changedFields;
    }

    /**
     * @return bool
     */
    public function isNewTicket()
    {
        return $this->hasChange('ticket.is_new');
    }

    /**
     * @return bool
     */
    public function isNewMessage()
    {
        return $this->hasChange('ticket.new_messages');
    }

    /**
     * @return array
     */
    public function getNewFollowers()
    {
        return array_diff($this->ticketB->followers, $this->ticketA->followers);
    }

    /**
     * Is there a change that might affect permissions?
     *
     * @return bool
     */
    public function isPermChange()
    {
        return $this->hasChange('ticket.department')
            || $this->hasChange('ticket.agent')
            || $this->hasChange('ticket.agent_team')
            || $this->hasChange('ticket.followers');
    }

    /**
     * @param string $fieldId
     *
     * @return bool
     */
    public function hasChange($fieldId)
    {
        $this->initChanged();

        return isset($this->changedFieldsMap[$fieldId]);
    }

    /**
     * @return TicketModel
     */
    public function getTicketA()
    {
        return $this->ticketA;
    }

    /**
     * @return TicketModel
     */
    public function getTicketB()
    {
        return $this->ticketB;
    }
}
