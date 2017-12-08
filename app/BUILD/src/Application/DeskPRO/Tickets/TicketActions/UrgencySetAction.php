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
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

/**
 * Sets the ticket urgency to a specifc value.
 */
class UrgencySetAction extends AbstractAction implements PermissionableAction
{
    /** @var int */
    protected $num;
    /** @var bool|null */
    protected $allow_lower;

    public function __construct($num, $allow_lower = null)
    {
        $this->num         = $num;
        $this->allow_lower = $allow_lower;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        if ($this->allow_lower || $ticket->urgency < $this->num) {
            $ticket['urgency'] = $this->num;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        if ($ticket['urgency'] == $this->num) {
            return [];
        }

        return [
            ['action' => 'urgency', 'urgency' => $this->num],
        ];
    }

    /**
     * Get the number modifier.
     *
     * @return int
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        return $otherAction;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $tr = App::getTranslator();

        if ($this->allow_lower) {
            return $tr->phrase('admin.tickets.set_urgency_to_x', ['urgency' => $this->num]);
        } else {
            return $tr->phrase('admin.tickets.set_urgency_to_x_when_lower', ['urgency' => $this->num]);
        }
    }
}
