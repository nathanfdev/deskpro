<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlBuilder;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlCondition;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlConditionGroup;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\TermGroup;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use Doctrine\DBAL\Connection;

class TicketSqlMatcher extends AbstractMatcher
{
    const ACTIVE = 'active';
    const ALL    = 'all';

    /**
     * @var string
     */
    private $mode;

    /**
     * @var Connection
     */
    private $db;

    /**
     * TicketSqlMatcher constructor.
     *
     * @param ValueResolver $valueResolver
     * @param array         $handlers
     * @param Connection    $db
     * @param string        $mode          TicketSqlMatcher::ACTIVE for active tickets, or TicketSqlMatcher::ALL for all tickets (slower)
     */
    public function __construct(ValueResolver $valueResolver, array $handlers, Connection $db, $mode)
    {
        parent::__construct($valueResolver, $handlers);
        $this->db = $db;

        if ($mode !== self::ACTIVE && $mode !== self::ALL) {
            throw new \InvalidArgumentException('Invalid mode');
        }

        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param Query   $query
     * @param Context $context
     *
     * @return SqlBuilder
     */
    public function getCountQueryBuilder(Query $query, Context $context)
    {
        $qb = $this->buildQueryBuilder($query, $context);
        $qb->select('COUNT(*)');

        return $qb;
    }

    /**
     * @param Query   $query
     * @param Context $context
     *
     * @return SqlBuilder
     */
    public function getIdsQueryBuilder(Query $query, Context $context)
    {
        $qb = $this->buildQueryBuilder($query, $context);
        $qb->select('tickets.id');

        return $qb;
    }

    /**
     * @param Query   $query
     * @param Context $context
     *
     * @return SqlBuilder
     */
    public function buildQueryBuilder(Query $query, Context $context)
    {
        $qb = new SqlBuilder($this->db);
        if ($this->mode === self::ACTIVE) {
            $qb->from('tickets_search_active', 'tickets');
        } else {
            $qb->from('tickets', 'tickets');
        }
        $qb->setMainTableAlias('tickets');

        $rootPart = $query->root;

        if ($rootPart instanceof TermGroup) {
            $condGroup = $this->buildTermGroup($rootPart, $context);
            $qb->addQueryConditionGroup($condGroup);
        } else {
            $cond = $this->buildTerm($rootPart, $context);
            if ($cond instanceof SqlConditionGroup) {
                $qb->addQueryConditionGroup($cond);
            } else {
                $qb->addQueryCondition($cond);
            }
        }

        return $qb;
    }

    /**
     * @param TermGroup $termGroup
     * @param Context   $context
     *
     * @return SqlConditionGroup
     */
    private function buildTermGroup(TermGroup $termGroup, Context $context)
    {
        $condGroup = new SqlConditionGroup($termGroup->operator->getOperator());

        foreach ($termGroup->terms as $term) {
            if ($term instanceof TermGroup) {
                $condGroup->add($this->buildTermGroup($term, $context));
            } else {
                $condGroup->add($this->buildTerm($term, $context));
            }
        }

        return $condGroup;
    }

    /**
     * @param Term    $term
     * @param Context $context
     *
     * @return SqlCondition
     */
    private function buildTerm(Term $term, Context $context)
    {
        $fieldId  = $term->field->identity;
        $operator = $term->operator->getOperator();

        // Top-level value is a function call,
        // see if we handle it with a special handler
        if ($match = $this->getMatchFunctionForTerm($term)) {
            /** @var TermsHandlerInterface $h */
            $h = $match[0];
            /** @var FunctionCompareDef $def */
            $def = $match[1];

            if (!in_array($operator, $def->operators)) {
                throw new \InvalidArgumentException("Cannot use function {$def->name} with operator {$term->operator->getOperator()}. Allowed operators: ".implode(', ', $def->operators));
            }

            return call_user_func(
                [$h, $def->matchFn],
                $fieldId,
                $operator,
                $this->getValueResovler()->getFuncCallParamValues($term->options->value, $term, $context),
                $context,
                $term
            );
        }

        $fieldHandlers = $this->getHandlersForFieldId($fieldId);

        if (empty($fieldHandlers)) {
            throw new \OutOfBoundsException("No handler is capable of handling $fieldId");
        }

        $options = $this->getValueResovler()->optionValueFromTerm($term, $context);

        $parts = [];
        foreach ($fieldHandlers as  $handler) {
            /** @var $handler TermsHandlerInterface */
            if ($c = $handler->buildQueryCondition(
                $fieldId,
                $operator,
                $options,
                $context,
                $term
            )) {
                if ($c) {
                    if (is_array($c)) {
                        $parts = array_merge($parts, $c);
                    } else {
                        $parts[] = $c;
                    }
                }
            }
        }

        if (empty($parts)) {
            $c = new SqlCondition();
            $c->setWhere('0');

            return $c;
        }

        if (count($parts) === 1) {
            return $parts[0];
        }

        $group = new SqlConditionGroup('AND');
        foreach ($parts as $p) {
            $group->add($p);
        }

        return $group;
    }

    /**
     * Get a SqlConditionGroup that will apply the provided agents permissions on to
     * a ticket filter query. If the agent can see everything, this will return null
     * (i.e. no additional conditions required for the agent).
     *
     * @param Agent $agent
     *
     * @return SqlConditionGroup|null
     */
    public static function buildPermissionConditionForAgent(Agent $agent)
    {
        // can view everything, no perms to apply
        if ($agent->canViewAll()) {
            return null;
        }

        // Agent can view IF....
        $condGroup = new SqlConditionGroup(SqlConditionGroup::OP_OR);

        // the ticket IS ASSIGNED to the agent or their teams
        $assignedPerms = new SqlConditionGroup(SqlConditionGroup::OP_OR);
        $assignedPerms->addCondition(SqlCondition::create()
            ->setWhere('{tickets}.agent_id = :agent_id')
            ->setParam('agent_id', $agent->id, \PDO::PARAM_INT)
        );
        if (!empty($agent->teams)) {
            $assignedPerms->addCondition(SqlCondition::create()
                ->setWhere('{tickets}.agent_team_id IN (:team_ids)')
                ->setParam('team_ids', $agent->teams, Connection::PARAM_INT_ARRAY)
            );
        }

        $condGroup->add($assignedPerms);

        $allowedDeps = $agent->allowed_departments;

        // OR the ticket is in a dep i can see (and its a state that i can see)
        if (!empty($allowedDeps) || $agent->all_departments_allowed) {
            $depCond = new SqlConditionGroup(SqlConditionGroup::OP_AND);

            // if we need to be explicit, then we need to check specific ids
            if (!$agent->all_departments_allowed) {
                $depCond->addCondition(SqlCondition::create()
                    ->setWhere('{tickets}.department_id IN (:allowed_dep_ids)')
                    ->setParam('allowed_dep_ids', $allowedDeps, Connection::PARAM_INT_ARRAY)
                );
            }

            if (!$agent->view_unassigned) {
                // but only if its not unassigned
                $depCond->addCondition(SqlCondition::create()
                    ->setWhere('({tickets}.agent_id IS NOT NULL OR {tickets}.agent_team_id IS NOT NULL)')
                );
            }

            if (!$agent->view_assigned) {
                // but only if its not assigned
                $depCond->addCondition(SqlCondition::create()
                    ->setWhere('({tickets}.agent_id IS NULL OR {tickets}.agent_team_id IS NULL)')
                );
            }

            if (!$depCond->isEmpty()) {
                $condGroup->add($depCond);
            }
        }

        return $condGroup;
    }
}
