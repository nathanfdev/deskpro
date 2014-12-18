<?php

namespace Application\DeskPRO\NewSearch\Repository;

use Elastica\Filter;

/**
 * Ticket Repository
 */
class TicketRepository extends AbstractRepository implements WithLabelsInterface
{
    /**
     * The currently logged in person.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * Fields to be highlighted
     *
     * @var array
     */
    protected $highlightFields = array(
        'subject'  => array('fragment_size' => 100),
        'messages' => array('fragment_size' => 100, 'number_of_fragments' => 1)
    );

    /**
     * Sets the person context
     *
     * @param $person
     */
    public function setPersonContext($person)
    {
        $this->person = $person;
    }

    /**
     * Constructs the filters array to handle agent permission
     *
     * @return array
     */
    protected function getFilters()
    {
        // See app/src/Application/DeskPRO/Searcher/TicketSearch.php
        // Re-creating permission logic via filters

        $main_filter = new Filter\BoolOr();

        $assigned_filter = new Filter\BoolOr();
        $assigned_filter->addFilter(new Filter\Term(array('agent' => $this->person->getId())));
        $team_ids = $this->person->getHelper('Agent')->getTeamIds();
        if ($team_ids) {
            $assigned_filter->addFilter(new Filter\Terms('agent_team', $team_ids));
        }

        $main_filter->addFilter($assigned_filter);

        if (!$this->person->getAllowedDepartments() || (!$this->person->hasPerm('agent_tickets.view_unassigned') && !$this->person->hasPerm('agent_tickets.view_others'))) {
            // cant see anything else
        } else {

            $sub_filter = new Filter\BoolAnd();
            $any = false;

            $dis_dep_ids = $this->person->getHelper('AgentPermissions')->getDisallowedDepartments();
            if ($dis_dep_ids) {
                $sub_filter->addFilter(new Filter\BoolNot(new Filter\Terms('department', $dis_dep_ids)));
                $any = true;
            }

            if (!$this->person->hasPerm('agent_tickets.view_unassigned')) {
                $not_assigned = new Filter\BoolOr();
                $not_assigned->addFilter(new Filter\BoolNot(new Filter\Term(array('agent' => 0))));
                $not_assigned->addFilter(new Filter\BoolNot(new Filter\Term(array('agent_team' => 0))));
                $sub_filter->addFilter($not_assigned);
                $any = true;
            }

            if (!$this->person->hasPerm('agent_tickets.view_others')) {
                $sub_filter->addFilter(new Filter\BoolNot(new Filter\Term(array('agent' => 0))));
                $sub_filter->addFilter(new Filter\BoolNot(new Filter\Term(array('agent_team' => 0))));
                $any = true;
            }


            // If user has all perms, then no filters are applied
            // and the BoolAnd filter will be empty
            if (!$any) {
                return array();
            }

            $main_filter->addFilter($sub_filter);
        }

        return $main_filter->toArray();
    }
}
