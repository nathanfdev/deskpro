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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketChangeTracker;

/**
 * Sets/removes on hold status.
 */
class HoldAction extends AbstractAction implements PermissionableAction
{
    /**
     * @var bool
     */
    protected $is_hold;

    /**
     * @var \Application\DeskPRO\Tickets\TicketChangeTracker
     */
    protected $tracker;

    public function __construct($is_hold, TicketChangeTracker $tracker = null)
    {
        $this->is_hold = (bool) $is_hold;
        $this->tracker = $tracker;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        // No change, sure they can apply no change
        if ($ticket->is_hold == $this->is_hold) {
            return true;
        }

        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'set_hold')) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $ticket->is_hold = $this->is_hold;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        if ($ticket->is_hold == $this->is_hold) {
            return [];
        }

        return [
            ['action' => 'hold', 'is_hold' => $this->is_hold],
        ];
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
        if ($this->is_hold) {
            return 'Put ticket on hold';
        } else {
            return 'Remove ticket from hold';
        }
    }
}
