<?php

namespace Application\DeskPRO\NewSearch\Repository;

use Application\DeskPRO\NewSearch\Filter\FilterInterface;
use Application\DeskPRO\NewSearch\Filter\AssignmentFilter;
use Application\DeskPRO\NewSearch\Filter\AgentTeamFilter;
use Application\DeskPRO\NewSearch\Filter\DepartmentFilter;
use Application\DeskPRO\NewSearch\Filter\ParticipationFilter;

/**
 * Ticket Repository
 */
class TicketRepository extends AbstractRepository
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
        $filters = array(
            new AssignmentFilter($this->person),
            new AgentTeamFilter($this->person),
            new ParticipationFilter($this->person),
            new DepartmentFilter($this->person)
        );

        $filterTree = array();

        /** @var FilterInterface $filter */
        foreach ($filters as $filter) {
            if ($result = $filter->getFilter()) {
                $filterTree[] = $result;
            }
        }

        return array(
            'or' => array(
                'filters' => $filterTree
            )
        );
    }
} 