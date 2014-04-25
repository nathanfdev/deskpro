<?php

namespace Application\DeskPRO\NewSearch\Repository;

use Elastica\Query\QueryString;
use FOS\ElasticaBundle\Repository;

use Application\DeskPRO\NewSearch\Filter\AssignmentFilter;
use Application\DeskPRO\NewSearch\Filter\AgentTeamFilter;
use Application\DeskPRO\NewSearch\Filter\DepartmentFilter;
use Application\DeskPRO\NewSearch\Filter\ParticipationFilter;

/**
 * Ticket Repository
 */
class TicketRepository extends Repository
{
    /**
     * The currently logged in person.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * Find
     *
     * Prepares an updated query object and passes back to parent function
     * for actual execution.
     *
     * @param $query
     * @param null $limit
     * @param array $options
     *
     * @return array
     */
    public function find($query, $limit = null, $options = array())
    {
        return parent::find($this->getQuery($query), $limit, $options);
    }

    /**
     * Constructs the raw query
     *
     * @param $q
     * @return array
     */
    private function getQuery($q)
    {
        $query = array(
            'query' => array(
                'filtered' => array(
                    'query'  => $this->getQueryString($q),
                    'filter' => $this->getFilters(),
                )
            )
        );

        return $query;
    }

    /**
     * Constructs the query string
     *
     * @param $q
     * @return array
     */
    private function getQueryString($q)
    {
        $queryString = new QueryString($q);
        $queryString->setDefaultOperator('AND');

        return $queryString->toArray();
    }

    /**
     * Constructs the filters array to handle agent permission
     *
     * @return array
     */
    private function getFilters()
    {
        $filters = array(
            new AssignmentFilter($this->person),
            new AgentTeamFilter($this->person),
            new ParticipationFilter($this->person),
            new DepartmentFilter($this->person)
        );

        $filterTree = array();

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

    /**
     * Sets the person context
     *
     * @param $person
     */
    public function setPersonContext($person)
    {
        $this->person = $person;
    }
} 