<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
