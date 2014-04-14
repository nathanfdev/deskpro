<?php

namespace Application\DeskPRO\NewSearch\Repository;

use FOS\ElasticaBundle\Repository;

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
                    'query' => $this->getMatch($q),
                    'filter' => array(
                        'or' => array(
                            'filters' => $this->getFilters(),
                        )
                    ),
                )
            )
        );

        return $query;
    }

    /**
     * Constructs the matching query
     *
     * In the case of a number being entered in quick search box, the search manager
     * will send the query in "_id:10" format (to be consistent with common repository
     * method calling). We thus need to check for this and handle accordingly.
     *
     * @param $q
     * @return array
     */
    private function getMatch($q)
    {
        if (substr($q, 0, 4) === '_id:') {
            $match = array(
                'match' => array('_id' => substr($q, 4))
            );
        } else {
            $match = array(
                'match' => array('_all' => $q)
            );
        }

        return $match;
    }

    /**
     * Constructs the filters array to handle agent permission
     *
     * @return array
     */
    private function getFilters()
    {
        $teams = $this->person->getHelper('Agent')->getTeamIds();
        $departments = $this->person->getHelper('AgentPermissions')->getAllowedDepartments();

        $filters = array(
            array('term' => array('agent' => $this->person->getId())),
            array('term' => array('agent_team' => $teams)),
            array('term' => array('participants' => $this->person->getId())),
            array('term' => array('department' => array_unique($departments)))
        );

        return $filters;
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