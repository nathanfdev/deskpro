<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Task;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

class TaskFilterBuilder
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var
     */
    protected $user;

    /**
     * Constructor
     * @param EntityManager $em
     * @param $user
     */
    public function __construct(EntityManager $em, Person $user)
    {
        $this->em = $em;
        $this->user = $user;
    }

    /**
     * Create a Doctrine query based on a request
     * @param ParameterBag $request
     * @return Query
     */
    public function filterRequest(ParameterBag $request)
    {
        $filter = $this->getFilter($request);

        if (empty($filter)) {
            $result =  $this->em->createQueryBuilder()->select('t')->from('App:Task', 't');
        } else {
            $result = $this->executeFilter($filter);
        }

        $result = $this->orderResults($result, $request);

        return $result->getQuery();
    }

    /**
     * Calculate what terms to filter the request by
     * @param ParameterBag $request
     * @return array
     */
    protected function getFilter(ParameterBag $request)
    {
        $filter = [];

        // List of allowed filters
        // NB We don't want to throw an exception if it's not in the list, as some other part of the API might use it
        // So we just ignore it instead
        $allowedFilters = [
            'assigned' => ['field' => 'person', 'table' => ['t.assigned', 'a']],
            'assigned_team' => ['field' => 'team', 'table' => ['t.assigned', 'a']],
            'assigned_department' => ['field' => 'department', 'table' => ['t.assigned', 'a']],
            'creator' => ['field' => 'creator'],
            'project' => ['field' => 'project'],
            'is_done' => ['field' => 'is_done'],
            'label' => ['field' => 'label', 'table' => ['t.labels', 'l']],
        ];

        foreach($request->all() as $item => $value) {
            if (in_array($item, array_keys($allowedFilters))) {
                // Add a NOT indicator
                $not = false;
                if (strpos($value, 'not_') === 0) {
                    $not = true;
                    $value = substr($value, 4);
                }

                // If the value is set to 'me', get the current user's ID, teams and departments
                if ($value === 'me') {
                    switch($allowedFilters[$item]['field']) {
                        case 'team':
                            $value = implode(',', $this->user->getTeamIds());
                            break;
                        case 'department':
                            // TODO perm_check
                            $this->user->loadHelper('AgentPermissions');
                            $value = implode(',', $this->user->getAllowedDepartments());
                            break;
                        default:
                            $value = $this->user->getId();
                    }
                } else if (!empty($value) && ($value === 'false' || $value === 'true')) {
                    // Clean false and true
                    $value = ($value === 'true');
                }

                // Add the not indicator back to the output
                if ($not) {
                    $value = 'not_' . $value;
                }

                $filter[$item] = $allowedFilters[$item];
                $filter[$item]['value'] = $value;
            }
        }

        return $filter;
    }

    /**
     * Build up a query according to the filter we need to process
     * @param $filter
     * @return QueryBuilder
     */
    protected function executeFilter($filter)
    {
        $query = $this->em->createQueryBuilder()->select('t')->from('App:Task', 't');

        // Join the necessary tables
        $joins = [];
        foreach ($filter as $param => $details) {
            if (!empty($details['table']) && !in_array($details['table'][0], $joins)) {
                $query = $query->leftJoin($details['table'][0], $details['table'][1]);
                $joins[] = $details['table'][0];
            }
        }

        // Set the where queries
        foreach ($filter as $param => $details) {
            // Work out whether there was a "not" indicator
            $not = false;
            if (strpos($details['value'], 'not_') === 0) {
                $not = true;
                $details['value'] = substr($details['value'], 4);
            }

            // Filter out null values
            $term = $not ? 'is NOT NULL' : 'is NULL';

            // If not null, see what kind of query it is
            if (!is_null($details['value'])) {
                $term = $not ? 'NOT IN (:' . $details['field'] . ')' : 'IN (:' . $details['field'] . ')';

                // If we don't have an array, check if it equals or doesn't equal
                if (strpos($details['value'], ',') === false) {
                    $term = $not ? '!= :' : '= :';
                    $term .= $details['field'];
                }

                // Set the query parameter
                $query = $query->setParameter($details['field'], $details['value']);
            }

            // Set the table to check
            $table = !empty($details['table']) ? $details['table'][1] : 't';
            $field = $table . '.' . $details['field'];

            // Put together the where clause
            $query = $query->andWhere($field . ' ' . $term);
        }

        return $query;
    }

    /**
     * @param QueryBuilder $query
     * @param ParameterBag $request
     * @return QueryBuilder
     */
    protected function orderResults(QueryBuilder $query, ParameterBag $request)
    {
        $mappings = [
            'due',
            'assigned',
            'created',
            'project'
        ];

        if (!$request->has('order') || !in_array($request->get('order'), $mappings)) {
            return $query;
        }

        switch($request->get('order')) {
            case 'due':
                $query = $query->orderBy('t.date_due', 'ASC');
                break;
            case 'assigned':
                break;
            case 'created':
                $query = $query->orderBy('t.date_created', 'DESC');
                break;
            case 'project':
                break;
        }

        return $query;
    }
}
