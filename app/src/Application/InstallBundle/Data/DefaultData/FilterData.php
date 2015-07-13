<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO.
 *
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeam\AgentTeamTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm;
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
        #------------------------------
        # Define filters
        #------------------------------

        $filter_set = new TicketFilterSet();
        $filter_set->setTitle('Awaiting Agent');
        $filter_set->setDefault(true);


        // my tickets
        $term = new CompositeTerm(array(), TermInterface::OP_AND);
        $term->addTerm(
            new AgentTerm(
                array(
                    'agent_ids' => array(AgentTerm::ID_ME)
                ),
                TermInterface::OP_IS
            )
        );
        $term->addTerm(
            new TicketStatusTerm(
                array(
                    'status' => array(Ticket::STATUS_AWAITING_AGENT)
                ),
                TermInterface::OP_IS
            )
        );
        $this->saveFilter('My Tickets', $term, $filter_set);


        // my team's tickets
        $term = new CompositeTerm(array(), TermInterface::OP_AND);
        $term->addTerm(
            new AgentTeamTerm(
                array(
                    'agent_team_ids' => array(AgentTeamTerm::TEAM_ID_ME)
                ),
                TermInterface::OP_IS
            )
        );
        $term->addTerm(
            new TicketStatusTerm(
                array(
                    'status' => array(Ticket::STATUS_AWAITING_AGENT)
                ),
                TermInterface::OP_IS
            )
        );
        $this->saveFilter('My Team\'s Tickets', $term, $filter_set);


        // tickets I follow
        $term = new CompositeTerm(array(), TermInterface::OP_AND);
        $term->addTerm(
            new TicketParticipantTerm(
                array(
                    'person_ids' => array(TicketParticipantTerm::ID_ME)
                ),
                TermInterface::OP_IS
            )
        );
        $term->addTerm(
            new TicketStatusTerm(
                array(
                    'status' => array(Ticket::STATUS_AWAITING_AGENT)
                ),
                TermInterface::OP_IS
            )
        );
        $this->saveFilter('Tickets I Follow', $term, $filter_set);


        // unassigned tickets
        $term = new CompositeTerm(array(), TermInterface::OP_AND);
        $term->addTerm(
            new AgentTerm(
                array(
                    'agent_ids' => array(0)
                ),
                TermInterface::OP_IS
            )
        );
        $term->addTerm(
            new AgentTeamTerm(
                array(
                    'agent_team_ids' => array(0)
                ),
                TermInterface::OP_IS
            )
        );
        $term->addTerm(
            new TicketStatusTerm(
                array(
                    'status' => array(Ticket::STATUS_AWAITING_AGENT)
                ),
                TermInterface::OP_IS
            )
        );
        $this->saveFilter('Unassigned', $term, $filter_set);


        // all tickets (awaiting agent)
        $term = new TicketStatusTerm(
            array(
                'status' => array(Ticket::STATUS_AWAITING_AGENT)
            ),
            TermInterface::OP_IS
        );
        $this->saveFilter('All', $term, $filter_set);


        // all tickets (awaiting user)
        $term = new TicketStatusTerm(
            array(
                'status' => array(Ticket::STATUS_AWAITING_USER)
            ),
            TermInterface::OP_IS
        );
        $this->saveFilter('Awaiting User', $term, $filter_set);


        // resolved
        $term = new TicketStatusTerm(
            array(
                'status' => array(Ticket::STATUS_RESOLVED)
            ),
            TermInterface::OP_IS
        );
        $this->saveFilter('Resolved', $term, $filter_set);


        // archived
        $term = new TicketStatusTerm(
            array(
                'status' => array(Ticket::STATUS_ARCHIVED)
            ),
            TermInterface::OP_IS
        );
        $this->saveFilter('Archived', $term, $filter_set);


        // awaiting validation
        $term = new TicketStatusTerm(
            array(
                'status' => array(Ticket::HIDDEN_STATUS_VALIDATING)
            ),
            TermInterface::OP_IS
        );
        $this->saveFilter('Awaiting Validation', $term, $filter_set);


        // spam
        $term = new TicketStatusTerm(
            array(
                'status' => array(Ticket::HIDDEN_STATUS_SPAM)
            ),
            TermInterface::OP_IS
        );
        $this->saveFilter('Spam', $term, $filter_set);


        // deleted
        $term = new TicketStatusTerm(
            array(
                'status' => array(Ticket::HIDDEN_STATUS_DELETED)
            ),
            TermInterface::OP_IS
        );
        $this->saveFilter('Deleted', $term, $filter_set);

        /////////
        // save

        $this->getEm()->persist($filter_set);
        $this->getEm()->flush();
    }

    private function oldFilterInstall()
    {
        #------------------------------
        # Define filters
        #------------------------------

        $filters = array();

        $filters[] = array(
            'title' => 'My Tickets',
            'sys_name' => 'agent',
            'order_by' => 'ticket.urgency:desc',
            'terms' => array(
                array('type' => 'agent', 'op' => 'is', 'options' => array('agent' => '-1')),
                array(
                    'type' => 'status',
                    'op' => 'is',
                    'options' => array('status' => 'awaiting_agent'),
                ),
            ),
        );

        $filters[] = array(
            'title' => 'My Team\'s Tickets',
            'sys_name' => 'agent_team',
            'order_by' => 'ticket.urgency:desc',
            'terms' => array(
                array('type' => 'agent_team', 'op' => 'is', 'options' => array('agent_team' => '-1')),
                array(
                    'type' => 'status',
                    'op' => 'is',
                    'options' => array('status' => 'awaiting_agent'),
                ),
            ),
        );

        $filters[] = array(
            'title' => 'Tickets I Follow',
            'sys_name' => 'participant',
            'order_by' => 'ticket.urgency:desc',
            'terms' => array(
                array('type' => 'participant', 'op' => 'is', 'options' => array('agent' => '-1')),
                array(
                    'type' => 'status',
                    'op' => 'is',
                    'options' => array('status' => 'awaiting_agent'),
                ),
            ),
        );

        $filters[] = array(
            'title' => 'Unassigned',
            'sys_name' => 'unassigned',
            'order_by' => 'ticket.urgency:desc',
            'terms' => array(
                array('type' => 'agent', 'op' => 'is', 'options' => array('agent' => '0')),
                array('type' => 'agent_team', 'op' => 'is', 'options' => array('agent_team' => '0')),
                array(
                    'type' => 'status',
                    'op' => 'is',
                    'options' => array('status' => 'awaiting_agent'),
                ),
            ),
        );

        $filters[] = array(
            'title' => 'All',
            'sys_name' => 'all',
            'order_by' => 'ticket.urgency:desc',
            'terms' => array(
                array(
                    'type' => 'status',
                    'op' => 'is',
                    'options' => array('status' => 'awaiting_agent'),
                ),
            ),
        );

        $filters[] = array(
            'title' => 'Awaiting User',
            'sys_name' => 'archive_awaiting_user',
            'order_by' => 'ticket.urgency:desc',
            'terms' => array(
                array(
                    'type' => 'status',
                    'op' => 'is',
                    'options' => array('status' => 'awaiting_user'),
                ),
            ),
        );

        $filters[] = array(
            'title' => 'Resolved',
            'sys_name' => 'archive_resolved',
            'order_by' => 'ticket.urgency:desc',
            'terms' => array(
                array(
                    'type' => 'status',
                    'op' => 'is',
                    'options' => array('status' => 'resolved'),
                ),
            ),
        );

        $filters[] = array(
            'title' => 'Archived',
            'sys_name' => 'archive_archived',
            'order_by' => 'ticket.urgency:desc',
            'terms' => array(
                array(
                    'type' => 'status',
                    'op' => 'is',
                    'options' => array('status' => 'archived'),
                ),
            ),
        );

        $filters[] = array(
            'title' => 'Awaiting Validation',
            'sys_name' => 'archive_validating',
            'order_by' => 'ticket.urgency:desc',
            'terms' => array(
                array(
                    'type' => 'status',
                    'op' => 'is',
                    'options' => array('status' => 'hidden.validating'),
                ),
            ),
        );

        $filters[] = array(
            'title' => 'Spam',
            'sys_name' => 'archive_spam',
            'order_by' => 'ticket.urgency:desc',
            'terms' => array(
                array(
                    'type' => 'status',
                    'op' => 'is',
                    'options' => array('status' => 'hidden.spam'),
                ),
            ),
        );

        $filters[] = array(
            'title' => 'Deleted',
            'sys_name' => 'archive_deleted',
            'order_by' => 'ticket.urgency:desc',
            'terms' => array(
                array(
                    'type' => 'status',
                    'op' => 'is',
                    'options' => array('status' => 'hidden.deleted'),
                ),
            ),
        );

        #------------------------------
        # Insert filters
        #------------------------------

        $exist_id_map = $this->getDb()->fetchAllKeyValue(
            "
            SELECT sys_name, id
            FROM ticket_filters
            WHERE sys_name IS NOT NULL
        "
        );

        $order = 1;
        foreach (array(0, 1) as $is_hold) {
            foreach ($filters as $f) {
                $is_archive = strpos($f['sys_name'], 'archive_') === 0;

                if ($is_archive && $is_hold) {
                    continue;
                }

                $f['is_global'] = 1;
                $f['is_enabled'] = 1;
                $f['display_order'] = $order++;

                if ($is_hold) {
                    $f['title'] .= ' (Hold)';
                    $f['sys_name'] .= '_w_hold';
                }

                if (!$is_archive) {
                    $f['terms'][] = array('type' => 'is_hold', 'op' => 'is', 'options' => array('is_hold' => $is_hold));
                }

                $f['terms'] = json_encode($f['terms']);

                $exist_id = isset($exist_id_map[$f['sys_name']]) ? $exist_id_map[$f['sys_name']] : null;
                if ($exist_id) {
                    $this->getDb()->update('ticket_filters', $f, array('id' => $exist_id));
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
