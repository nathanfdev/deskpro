<?php

/**
 * DeskPRO.
 *
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeam\AgentTeamTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketParticipant\TicketParticipantTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus\TicketStatusTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class FilterData extends AbstractDefaultData
{
    public function runInstall()
    {
        // TODO: remove oldFilterInstall when newFilterInstall takes over
        $this->oldFilterInstall();
        $this->newFilterInstall();
    }

    private function newFilterInstall()
    {
        $status_agent = new TicketStatusTerm(['status' => [Ticket::STATUS_AWAITING_AGENT]], TermInterface::OP_IS);
        $status_user  = new TicketStatusTerm(['status' => [Ticket::STATUS_AWAITING_USER]], TermInterface::OP_IS);

        //------------------------------
        // Inbox
        //------------------------------

        $filter_set = new TicketFilterSet();
        $filter_set->setTitle('Inbox');
        $filter_set->setIsDefault(true);
        $filter_set->setDisplayOrder(10);
        $this->getEm()->persist($filter_set);

        // my tickets
        $term = new CompositeTerm([], TermInterface::OP_AND);
        $term->addTerm(new AgentTerm(['agent_ids' => [AgentTerm::ID_ME]], TermInterface::OP_IS));
        $term->addTerm($status_agent);
        $this->saveFilter('My Tickets', $term, $filter_set);

        // my team's tickets
        $term = new CompositeTerm([], TermInterface::OP_AND);
        $term->addTerm(new AgentTeamTerm(['agent_team_ids' => [AgentTeamTerm::TEAM_ID_ME]], TermInterface::OP_IS));
        $term->addTerm($status_agent);
        $this->saveFilter('My Team\'s Tickets', $term, $filter_set);

        // tickets I follow
        $term = new CompositeTerm([], TermInterface::OP_AND);
        $term->addTerm(new TicketParticipantTerm(['person_ids' => [TicketParticipantTerm::ID_ME]], TermInterface::OP_IS));
        $term->addTerm($status_agent);
        $this->saveFilter('Tickets I Follow', $term, $filter_set);

        // unassigned tickets
        $term = new CompositeTerm([], TermInterface::OP_AND);
        $term->addTerm(new AgentTerm(['agent_ids' => [0]], TermInterface::OP_IS));
        $term->addTerm(new AgentTeamTerm(['agent_team_ids' => [0]], TermInterface::OP_IS));
        $term->addTerm($status_agent);
        $this->saveFilter('Unassigned', $term, $filter_set);

        // all tickets (awaiting agent)
        $term = new TicketStatusTerm(['status' => [Ticket::STATUS_AWAITING_AGENT]], TermInterface::OP_IS);
        $this->saveFilter('All', $term, $filter_set);

        //------------------------------
        // All Tickets
        //------------------------------

        $filter_set = new TicketFilterSet();
        $filter_set->setTitle('All Tickets');
        $filter_set->setIsDefault(true);
        $filter_set->setDisplayOrder(20);
        $this->getEm()->persist($filter_set);

        // mine on hold
        // TODO correct the term
        $term = $status_agent;
        $this->saveFilter('Mine On Hold', $term, $filter_set);

        // all on hold
        // TODO correct the term
        $term = $status_agent;
        $this->saveFilter('All On Hold', $term, $filter_set);

        // my recent activity
        // TODO correct the term
        $term = $status_agent;
        $this->saveFilter('My Recent Activity', $term, $filter_set);

        // aging
        // TODO correct the term
        $term = $status_agent;
        $this->saveFilter('Aging', $term, $filter_set);

        // my recent activity
        // TODO correct the term
        $term = $status_agent;
        $this->saveFilter('New (opened today)', $term, $filter_set);

        // my awaiting user
        $term = new CompositeTerm([], TermInterface::OP_AND);
        $term->addTerm(new AgentTerm(['agent_ids' => [AgentTerm::ID_ME]], TermInterface::OP_IS));
        $term->addTerm($status_user);
        $this->saveFilter('My Awaiting User', $term, $filter_set);

        // all awaiting user
        $term = $status_user;
        $this->saveFilter('All Awaiting User', $term, $filter_set);

        // resolved
        $term = new TicketStatusTerm(['status' => [Ticket::STATUS_RESOLVED]], TermInterface::OP_IS);
        $this->saveFilter('Resolved', $term, $filter_set);

        // archived
        $term = new TicketStatusTerm(['status' => [Ticket::STATUS_ARCHIVED]], TermInterface::OP_IS);
        $this->saveFilter('Archived', $term, $filter_set);

        // spam
        $term = new TicketStatusTerm(['status' => [Ticket::HIDDEN_STATUS_SPAM]], TermInterface::OP_IS);
        $this->saveFilter('Spam', $term, $filter_set);

        // deleted
        $term = new TicketStatusTerm(['status' => [Ticket::HIDDEN_STATUS_DELETED]], TermInterface::OP_IS);
        $this->saveFilter('Deleted', $term, $filter_set);

        /////////
        // save

        $this->getEm()->flush();
    }

    private function oldFilterInstall()
    {
        //------------------------------
        // Define filters
        //------------------------------

        $filters = [];

        $filters[] = [
            'title'    => 'My Tickets',
            'sys_name' => 'agent',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                ['type' => 'agent', 'op' => 'is', 'options' => ['agent' => '-1']],
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'awaiting_agent'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'My Team\'s Tickets',
            'sys_name' => 'agent_team',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                ['type' => 'agent_team', 'op' => 'is', 'options' => ['agent_team' => '-1']],
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'awaiting_agent'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'Tickets I Follow',
            'sys_name' => 'participant',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                ['type' => 'participant', 'op' => 'is', 'options' => ['agent' => '-1']],
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'awaiting_agent'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'Unassigned',
            'sys_name' => 'unassigned',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                ['type' => 'agent', 'op' => 'is', 'options' => ['agent' => '0']],
                ['type' => 'agent_team', 'op' => 'is', 'options' => ['agent_team' => '0']],
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'awaiting_agent'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'All',
            'sys_name' => 'all',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'awaiting_agent'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'Awaiting User',
            'sys_name' => 'archive_awaiting_user',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'awaiting_user'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'Resolved',
            'sys_name' => 'archive_resolved',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'resolved'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'Archived',
            'sys_name' => 'archive_archived',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'archived'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'Spam',
            'sys_name' => 'archive_spam',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'hidden.spam'],
                ],
            ],
        ];

        $filters[] = [
            'title'    => 'Deleted',
            'sys_name' => 'archive_deleted',
            'order_by' => 'ticket.urgency:desc',
            'terms'    => [
                [
                    'type'    => 'status',
                    'op'      => 'is',
                    'options' => ['status' => 'hidden.deleted'],
                ],
            ],
        ];

        //------------------------------
        // Insert filters
        //------------------------------

        $exist_id_map = $this->getDb()->fetchAllKeyValue(
            '
            SELECT sys_name, id
            FROM ticket_filters
            WHERE sys_name IS NOT NULL
        '
        );

        $order = 1;
        foreach ([0, 1] as $is_hold) {
            foreach ($filters as $f) {
                $is_archive = strpos($f['sys_name'], 'archive_') === 0;

                if ($is_archive && $is_hold) {
                    continue;
                }

                $f['is_global']     = 1;
                $f['is_enabled']    = 1;
                $f['display_order'] = $order++;

                if ($is_hold) {
                    $f['title'] .= ' (Hold)';
                    $f['sys_name'] .= '_w_hold';
                }

                if (!$is_archive) {
                    $f['terms'][] = ['type' => 'is_hold', 'op' => 'is', 'options' => ['is_hold' => $is_hold]];
                }

                $f['terms'] = json_encode($f['terms']);

                $exist_id = isset($exist_id_map[$f['sys_name']]) ? $exist_id_map[$f['sys_name']] : null;
                if ($exist_id) {
                    $this->getDb()->update('ticket_filters', $f, ['id' => $exist_id]);
                } else {
                    $this->getDb()->insert('ticket_filters', $f);
                }
            }
        }
    }

    public function runReset()
    {
        $this->runInstall();
    }

    public function runSync()
    {
        $this->runInstall();
    }

    /**
     * @param $filter_name
     * @param $term
     * @param $filter_set
     */
    private function saveFilter($filter_name, TermInterface $term, TicketFilterSet $filter_set)
    {
        $filter = new TicketFilter();
        $filter->setTitle($filter_name);
        $filter->setTerm($term);
        $filter_set->addFilter($filter);

        $this->getEm()->persist($filter);
    }
}
