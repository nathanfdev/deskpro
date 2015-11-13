<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Task;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @deprecated Use \DeskPRO\Bundle\AppBundle\DataService\Task\TaskSelectCriteria instead
 */
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
     * The tables which have been joined.
     *
     * @var array
     */
    protected $joins = [];

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param $user
     */
    public function __construct(EntityManager $em, Person $user)
    {
        $this->em   = $em;
        $this->user = $user;
    }

    /**
     * Create a Doctrine query based on a request.
     *
     * @param ParameterBag $request
     *
     * @return Query
     */
    public function filterRequest(ParameterBag $request)
    {
        $filter = $this->getFilter($request);

        if (empty($filter)) {
            $queryBuilder = $this->em->createQueryBuilder();
            $result       = $queryBuilder->select('t')->from('App:Task', 't');
        } else {
            $result = $this->executeFilter($filter);
        }

        $result = $this->orderResults($result, $request);

        return $result->getQuery();
    }

    /**
     * Calculate what terms to filter the request by.
     *
     * @param ParameterBag $request
     *
     * @return array
     */
    protected function getFilter(ParameterBag $request)
    {
        $filter = [];

        // List of allowed filters
        // NB We don't want to throw an exception if it's not in the list, as some other part of the API might use it
        // So we just ignore it instead
        $allowedFilters = [
            'ids'                 => ['field' => 'id'],
            'assigned'            => ['field' => 'person', 'table' => ['t.assigned', 'a']],
            'assigned_team'       => ['field' => 'team', 'table' => ['t.assigned', 'a']],
            'assigned_department' => ['field' => 'department', 'table' => ['t.assigned', 'a']],
            'creator'             => ['field' => 'creator'],
            'project'             => ['field' => 'project'],
            'is_done'             => ['field' => 'is_done'],
            'labels'              => ['field' => 'label', 'table' => ['t.labels', 'lab']],
            'attachments'         => ['field' => 'id', 'table' => ['t.attachments', 'at']],
            'list'                => ['field' => 'list'],
            'due'                 => ['field' => 'date_due'],
            'created'             => ['field' => 'date_created'],
            'done'                => ['field' => 'date_done'],
            'due_after'           => ['field' => 'date_due'],
            'created_after'       => ['field' => 'date_created'],
            'done_after'          => ['field' => 'date_done'],
            'due_before'          => ['field' => 'date_due'],
            'created_before'      => ['field' => 'date_created'],
            'done_before'         => ['field' => 'date_done'],
        ];

        foreach ($request->all() as $item => $value) {
            if (in_array($item, array_keys($allowedFilters))) {
                // Add a NOT indicator
                $not = false;
                if (strpos($value, 'not_') === 0) {
                    $not   = true;
                    $value = substr($value, 4);
                }

                // If the value is set to 'me', get the current user's ID, teams and departments
                if ($value === 'me') {
                    switch ($allowedFilters[$item]['field']) {
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
                } elseif (!empty($value) && ($value === 'now')) {
                    $datetime = new \DateTime();
                    $value    = $datetime->format('c');
                } elseif (!empty($value) && ($value === 'false' || $value === 'true')) {
                    // Clean false and true
                    $value = ($value === 'true');
                }

                // Add the not indicator back to the output
                if ($not) {
                    $value = 'not_'.$value;
                }

                $filter[$item]          = $allowedFilters[$item];
                $filter[$item]['value'] = $value;
            }
        }

        return $filter;
    }

    /**
     * Build up a query according to the filter we need to process.
     *
     * @param $filter
     *
     * @return QueryBuilder
     */
    protected function executeFilter($filter)
    {
        $query = $this->em->createQueryBuilder()->select('t')->from('App:Task', 't');

        // Join the necessary tables
        foreach ($filter as $param => $details) {
            if (!empty($details['table']) && !in_array($details['table'][0], $this->joins)) {
                $query         = $query->leftJoin($details['table'][0], $details['table'][1]);
                $this->joins[] = $details['table'][0];
            }
        }

        // Set the where queries
        foreach ($filter as $param => $details) {
            // Work out whether there was a "not" indicator
            $not = false;
            if (strpos($details['value'], 'not_') === 0) {
                $not              = true;
                $details['value'] = substr($details['value'], 4);
            }

            // Sort out before and after indicators
            $after  = strrpos($param, '_after') === (strlen($param) - 6);
            $before = strrpos($param, '_before') === (strlen($param) - 7);

            // Filter out null values
            $term = $not ? 'is NOT NULL' : 'is NULL';

            // If not null, see what kind of query it is
            if (!is_null($details['value']) && $details['value'] !== 'null') {
                $term = $not ? 'NOT IN (:'.$param.')' : 'IN (:'.$param.')';

                // If we don't have an array, check if it equals or doesn't equal
                if (strpos($details['value'], ',') === false) {
                    if ($after) {
                        $term = $not ? '<= :' : '> :';
                    } elseif ($before) {
                        $term = $not ? '>= :' : '< :';
                    } else {
                        $term = $not ? '!= :' : '= :';
                    }

                    $term .= $param;

                    // Set the query parameter
                    $query = $query->setParameter($param, $details['value']);
                } else {
                    $query = $query->setParameter($param, explode(',', $details['value']));
                }
            }

            // Set the table to check
            $table = !empty($details['table']) ? $details['table'][1] : 't';
            $field = $table.'.'.$details['field'];

            // Put together the where clause
            $query = $query->andWhere($field.' '.$term);
        }

        return $query;
    }

    /**
     * Add an orderBy clause to the results.
     *
     * @param QueryBuilder $query        The current query
     * @param ParameterBag $request      The request
     * @param QueryBuilder $queryBuilder The builder used to create the query
     *
     * @return QueryBuilder
     */
    protected function orderResults(QueryBuilder $query, ParameterBag $request)
    {
        $mappings = [
            'due',
            'assigned',
            'created',
            'project',
            'list',
            'creator',
            'done',
            'labels',
            'ticket',
        ];

        // Kick it out if the request doesn't have the correct mapping
        if (!$request->has('order_by') || !in_array($request->get('order_by'), $mappings)) {
            return $query;
        }

        switch ($request->get('order_by')) {
            case 'due':
                $query     = $query->addSelect('COALESCE(t.date_due, \'2999-12-31 12:59:59\') AS HIDDEN sort_date');
                $direction = $this->getSortDirection($request);
                $query     = $query->orderBy('sort_date', $direction);
                break;
            case 'assigned':

                if (!in_array('t.assigned', $this->joins)) {
                    $query = $query->leftJoin('t.assigned', 'a');
                }

                $query = $query->addSelect('CASE WHEN (IDENTITY(a.person) IS NOT NULL)
                    THEN 1
                    ELSE
                        CASE WHEN (IDENTITY(a.team) IS NOT NULL)
                        THEN 2
                        ELSE
                            CASE WHEN (IDENTITY(a.department) IS NOT NULL)
                            THEN 3
                            ELSE 0
                            END
                        END
                    END
                    AS HIDDEN assignment_type');

                if (!in_array('a.person', $this->joins)) {
                    $query = $query->leftJoin('a.person', 'pe');
                }
                if (!in_array('a.team', $this->joins)) {
                    $query = $query->leftJoin('a.team', 'te');
                }
                if (!in_array('a.department', $this->joins)) {
                    $query = $query->leftJoin('a.department', 'de');
                }

                $query = $query->addSelect('COALESCE(pe.name, te.name, de.title)
                    AS HIDDEN assignee_name');
                $query = $query->addOrderBy('assignment_type', 'ASC');
                $query = $query->addOrderBy('assignee_name', 'ASC');

                break;
            case 'created':
                $direction = $this->getSortDirection($request, 'DESC');
                $query     = $query->orderBy('t.date_created', $direction);
                break;
            case 'project':
                if (!in_array('t.projects', $this->joins)) {
                    $query = $query->leftJoin('t.project', 'p');
                }

                $direction = $this->getSortDirection($request);

                $query = $query->orderBy('p.title', $direction);
                break;
            case 'creator':
                $query     = $query->leftJoin('t.creator', 'c');
                $direction = $this->getSortDirection($request);

                $query = $query->addOrderBy('c.name', $direction);
                break;
            case 'list':
                $direction = $this->getSortDirection($request);
                $query     = $query->orderBy('t.list', $direction)->addOrderBy('t.display_order', 'ASC');
                break;
            case 'done':
                $direction = $this->getSortDirection($request, 'DESC');
                $query     = $query->addSelect('COALESCE(t.date_done, \'2999-12-31 12:59:59\') AS HIDDEN sort_date');
                $query     = $query->addOrderBy('sort_date', $direction);
                break;
            case 'labels':
                $direction = $this->getSortDirection($request);
                $query     = $query->leftJoin('t.labels', 'l');
                $query     = $query->addSelect("group_concat(l.label ORDER BY l.label SEPARATOR ',') AS HIDDEN labelGroup");
                $query     = $query->groupBy('t.id');
                $query     = $query->addOrderBy('labelGroup', $direction);
                break;
            case 'ticket':
                $direction = $this->getSortDirection($request);
                $query     = $query->leftJoin('t.linked_items', 'l', 'WITH', 'l.ticket != \'NULL\'');
                $query     = $query->addOrderBy('l.ticket', $direction);
                break;
        }

        return $query;
    }

    /**
     * Retrieve, validate, format and return the direction to sort
     * Defaults to ascending.
     *
     *
     * @param ParameterBag $request
     * @param string       $default
     *
     * @throws \LogicException
     *
     * @return string
     */
    protected function getSortDirection(ParameterBag $request, $default = 'ASC')
    {
        $valid = ['ASC', 'DESC'];

        if (!in_array($default, $valid)) {
            throw new \LogicException('Default sort direction must be ASC or DESC');
        }

        if (!$request->has('sort') || !in_array(strtoupper($request->get('sort')), $valid)) {
            return $default;
        }

        return strtoupper($request->get('sort'));
    }
}
