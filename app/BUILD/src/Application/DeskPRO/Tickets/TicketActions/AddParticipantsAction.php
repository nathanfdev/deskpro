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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;

/**
 * Adds participants.
 */
class AddParticipantsAction extends AbstractAction
{
    /** @var array */
    protected $add_people_ids;

    public function __construct(array $add_participants)
    {
        $this->add_people_ids = $add_participants;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $people = App::getEntityRepository('DeskPRO:Person')->getPeopleFromIds($this->add_people_ids);
        foreach ($people as $person) {
            $ticket->addParticipantPerson($person);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        $actions = [];

        foreach ($this->add_people_ids as $pid) {
            $actions[] = [
                'action'    => 'add_participant',
                'person_id' => $pid,
            ];
        }

        return $actions;
    }

    /**
     * Get the agent id.
     *
     * @return int
     */
    public function getPersonIds()
    {
        return $this->add_people_ids;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        $ids = $this->getPersonIds();
        $ids = array_merge($ids, $otherAction->getPersonIds());
        $ids = array_unique($ids);

        return new self($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $agents = [];
        $users  = [];

        $people = App::getEntityRepository('DeskPRO:Person')->getPeopleFromIds($this->add_people_ids);

        foreach ($people as $p) {
            $n = $as_html ? htmlspecialchars($p->getDisplayName()) : $p->getDisplayName();
            if ($p->is_agent) {
                $agents[$p->id] = $n;
            } else {
                $users[$p->id] = $n;
            }
        }

        $parts = [];
        if ($agents) {
            $parts[] = 'Add agent followers: '.implode(', ', $agents);
        }
        if ($users) {
            $parts[] = 'CC users '.implode(', ', $users);
        }

        if (!$parts) {
            return '';
        }

        $parts = implode(' and ', $parts);

        return $parts;
    }
}
