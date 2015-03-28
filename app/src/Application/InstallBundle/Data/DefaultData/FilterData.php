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
use DeskPRO\Bundle\AppBundle\Entity\Filter;
use DeskPRO\Bundle\AppBundle\Entity\FilterSet;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeamTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketParticipantTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatusTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class FilterData extends AbstractDefaultData
{
    public function runInstall()
    {
        #------------------------------
        # Define filters
        #------------------------------

        $filter_set = new FilterSet();
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
                    'agent_ids' => array(AgentTerm::ID_UNASSIGNED)
                ),
                TermInterface::OP_IS
            )
        );
        $term->addTerm(
            new AgentTeamTerm(
                array(
                    'agent_team_ids' => array(AgentTeamTerm::TEAM_ID_UNASSIGNED)
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
                'status' => array(Ticket::STATUS_HIDDEN . '.' . Ticket::HIDDEN_STATUS_VALIDATING)
            ),
            TermInterface::OP_IS
        );
        $this->saveFilter('Awaiting Validation', $term, $filter_set);


        // spam
        $term = new TicketStatusTerm(
            array(
                'status' => array(Ticket::STATUS_HIDDEN . '.' . Ticket::HIDDEN_STATUS_SPAM)
            ),
            TermInterface::OP_IS
        );
        $this->saveFilter('Spam', $term, $filter_set);


        // deleted
        $term = new TicketStatusTerm(
            array(
                'status' => array(Ticket::STATUS_HIDDEN . '.' . Ticket::HIDDEN_STATUS_DELETED)
            ),
            TermInterface::OP_IS
        );
        $this->saveFilter('Deleted', $term, $filter_set);

        /////////
        // save

        $this->getEm()->persist($filter_set);
        $this->getEm()->flush();
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
    private function saveFilter($filter_name, TermInterface $term, FilterSet $filter_set)
    {
        $filter = new Filter();
        $filter->setTitle($filter_name);
        $filter->setTerm($term);
        $filter_set->addFilter($filter);

        $this->getEm()->persist($filter);
    }
}
