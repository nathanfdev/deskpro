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

        $main_filter = new Filter\BoolAnd();

        $assigned_filter = new Filter\BoolOr();
        $assigned_filter->addFilter(new Filter\Term(array('agent' => $this->person->getId())));
        $team_ids = $this->person->getHelper('Agent')->getTeamIds();
        if ($team_ids) {
            $assigned_filter->addFilter(new Filter\Terms('agent_team', $team_ids));
        }

        $dis_dep_ids = $this->person->getHelper('AgentPermissions')->getDisallowedDepartments();
        if ($dis_dep_ids) {
            $perm1 = new Filter\BoolOr();
            $perm1->addFilter(new Filter\BoolNot(new Filter\Terms('department', $dis_dep_ids)));
            $perm1->addFilter($assigned_filter);
            $main_filter->addFilter($perm1);
        }

        if (!$this->person->hasPerm('agent_tickets.view_unassigned')) {
            $main_filter->addFilter(new Filter\BoolNot(new Filter\Term(array('agent' => 0))));
        }

        if (!$this->person->hasPerm('agent_tickets.view_others')) {
            $perm2 = new Filter\BoolOr();
            $perm2->addFilter($assigned_filter);
            if ($this->person->hasPerm('agent_tickets.view_unassigned')) {
                $perm2->addFilter(new Filter\Term(array('agent' => 0)));
            }

            $main_filter->addFilter($perm2);
        }

        // If user has all perms, then no filters are applied
        // and the BoolAnd filter will be empty
        if (!$main_filter->getFilters()) {
            return array();
        }

        return $main_filter->toArray();
    }
}
