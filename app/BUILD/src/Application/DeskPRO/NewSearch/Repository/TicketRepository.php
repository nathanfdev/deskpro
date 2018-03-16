<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace Application\DeskPRO\NewSearch\Repository;

use Elastica\Query;

/**
 * Ticket Repository.
 */
class TicketRepository extends AbstractRepository implements WithLabelsInterface
{
    /**
     * The currently logged in person.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    protected $highlightFields = [
        'subject'  => ['fragment_size' => 100],
        'messages' => ['fragment_size' => 100, 'number_of_fragments' => 1],
    ];

    /**
     * {@inheritdoc}
     */
    protected function getSource()
    {
        return ['excludes' => ['attachment']];
    }

    /**
     * {@inheritdoc}
     */
    protected function getQueryFields()
    {
        return ['_all', 'attachments'];
    }

    /**
     * Sets the person context.
     *
     * @param $person
     */
    public function setPersonContext($person)
    {
        $this->person = $person;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilters(array $options = [])
    {
        // See app/src/Application/DeskPRO/Searcher/TicketSearch.php
        // Re-creating permission logic via filters

        $main_filter = new Query\BoolQuery();

        $assigned_filter = new Query\BoolQuery();
        $assigned_filter->addShould(new Query\Term(['agent' => $this->person->getId()]));

        $this->person->loadHelper('Agent');
        $teamIds = $this->person->getHelper('Agent')->getTeamIds();
        if ($teamIds) {
            $assigned_filter->addShould(new Query\Terms('agent_team', $teamIds));
        }

        $main_filter->addShould($assigned_filter);
        $this->person->loadHelper('AgentPermissions');

        if (!$this->person->getAllowedDepartments() || (!$this->person->hasPerm('agent_tickets.view_unassigned') && !$this->person->hasPerm('agent_tickets.view_others'))) {
            // cant see anything else
        } else {
            $sub_filter = new Query\BoolQuery();
            $any        = false;

            $dis_dep_ids = $this->person->getDisallowedDepartments();
            if ($dis_dep_ids) {
                $sub_filter->addMustNot(new Query\Terms('department', $dis_dep_ids));
                $any = true;
            }

            if (!$this->person->hasPerm('agent_tickets.view_unassigned')) {
                $setAgentQuery = new Query\BoolQuery();
                $setAgentQuery->addMustNot(new Query\Term(['agent' => 0]));

                $setAgentTeamQuery = new Query\BoolQuery();
                $setAgentTeamQuery->addMustNot(new Query\Term(['agent_team' => 0]));

                $not_assigned = new Query\BoolQuery();
                $not_assigned->addShould($setAgentQuery);
                $not_assigned->addShould($setAgentTeamQuery);
                $sub_filter->addMust($not_assigned);
                $any = true;
            }

            if (!$this->person->hasPerm('agent_tickets.view_others')) {
                $sub_filter->addMustNot(new Query\Term(['agent' => 0]));
                $sub_filter->addMustNot(new Query\Term(['agent_team' => 0]));
                $any = true;
            }

            // If user has all perms, then no filters are applied
            // and the BoolAnd filter will be empty
            if (!$any) {
                return [];
            }

            $main_filter->addShould($sub_filter);
        }

        return $main_filter->toArray();
    }
}
