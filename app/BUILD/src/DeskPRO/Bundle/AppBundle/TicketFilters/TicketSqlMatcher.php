<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Tickets\GroupingCounter;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\CustomField;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlBuilder;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlCondition;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlConditionGroup;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\SqlTermHandlerInterface;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\TermGroup;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\Util\DebugUtils;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\StructComparer;
use Doctrine\DBAL\Connection;

/**
 * Class TicketSqlMatcher.
 */
class TicketSqlMatcher extends AbstractMatcher
{
    const ACTIVE = 'active';
    const ALL    = 'all';

    /**
     * @var SqlTermHandlerInterface[]
     */
    private $handlers;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var CustomFieldSet
     */
    private $customFieldSet;

    /**
     * @var OptionMapperInterface
     */
    private $fieldOptionMapper;

    /**
     * @var bool
     */
    private $applyContextPermissions = true;

    /**
     * TicketSqlMatcher constructor.
     *
     * @param ValueResolver             $valueResolver
     * @param SqlTermHandlerInterface[] $handlers
     * @param Connection                $db
     * @param string                    $mode              TicketSqlMatcher::ACTIVE for active tickets, or TicketSqlMatcher::ALL for all tickets (slower)
     * @param CustomFieldSet            $customFieldSet
     * @param OptionMapperInterface     $fieldOptionMapper
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(ValueResolver $valueResolver, array $handlers, Connection $db, $mode, CustomFieldSet $customFieldSet = null, OptionMapperInterface $fieldOptionMapper = null)
    {
        parent::__construct($valueResolver);
        $this->db = $db;

        if ($mode !== self::ACTIVE && $mode !== self::ALL) {
            throw new \InvalidArgumentException('Invalid mode');
        }

        $this->handlers          = $handlers;
        $this->mode              = $mode;
        $this->customFieldSet    = $customFieldSet ?: new CustomFieldSet();
        $this->fieldOptionMapper = $fieldOptionMapper;
    }

    /**
     * Enable context permissions on the query builder.
     */
    public function enableContextPermissions()
    {
        $this->applyContextPermissions = true;
    }

    /**
     * Disable context permissions on the query builder.
     */
    public function disableContextPermissions()
    {
        $this->applyContextPermissions = false;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param Query              $query
     * @param Context            $context
     * @param TicketSearchParams $params
     *
     * @return SqlBuilder
     */
    public function getCountQueryBuilder(Query $query, Context $context, TicketSearchParams $params = null)
    {
        $qb = $this->buildQueryBuilder($query, $context);
        $qb->select('COUNT(*) AS count');

        if ($params) {
            $this->addGroupBy($qb, $params->getGroupFields());
            $this->addSubFilterBy($qb, $params->getSubFilterFields());

            if ($params->hasGroupFields()) {
                $qb->enableWithRollup();
            }
        }

        $this->logger->debug(sprintf(
            '[TicketSqlMatcher::getCountQueryBuilder] Query: %s -- SQL: %s -- Params: %s',
            $query->fql ?: print_r($query, 1),
            $qb->getSQL(),
            DebugUtils::varToString($qb->getParameters())
        ));

        return $qb;
    }

    /**
     * @param Query              $query
     * @param Context            $context
     * @param TicketSearchParams $params
     *
     * @return SqlBuilder
     */
    public function getIdsQueryBuilder(Query $query, Context $context, TicketSearchParams $params = null)
    {
        $qb = $this->buildQueryBuilder($query, $context);
        $qb->select('tickets.id');

        if ($params) {
            $this->addOrderBy($qb, $params->getOrderFields());
            $this->addSubFilterBy($qb, $params->getSubFilterFields());
        }

        if (!$params || !$params->hasOrderFields()) {
            $qb->orderBy('tickets.id', 'DESC');
        }

        $this->logger->debug(sprintf(
            '[TicketSqlMatcher::getIdsQueryBuilder] Query: %s -- SQL: %s -- Params: %s',
            $query->fql ?: print_r($query, 1),
            $qb->getSQL(),
            DebugUtils::varToString($qb->getParameters())
        ));

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

        if ($rootPart === null) {
            return $qb;
        }

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

        if ($this->applyContextPermissions) {
            $agentCond = self::buildPermissionConditionForAgent($context->getAgent());
            if ($agentCond) {
                $qb->addQueryConditionGroup($agentCond);
            }
        }

        return $qb;
    }

    /**
     * @param SqlBuilder $qb
     * @param array      $groupFields
     */
    private function addGroupBy(SqlBuilder $qb, array $groupFields)
    {
        if (empty($groupFields)) {
            return;
        }

        foreach ($groupFields as $idx => $fieldId) {
            $selectId = "group_field{$idx}";
            $joinId   = 'grouping'.$idx;

            $fieldInfo = TicketSearchParams::parseFieldId($fieldId);

            switch ($fieldInfo['type']) {
                case TicketSearchParams::GROUP_STATUS:
                    $qb->addSelect("tickets.status AS $selectId");
                    $qb->addGroupBy('tickets.status');
                    break;

                case TicketSearchParams::GROUP_SLA_SEVERITY:
                    $qb->addSelect("MAX(FIELD($joinId.sla_status, 'ok', 'warning', 'fail')) AS $selectId");
                    $qb->leftJoin('tickets', 'ticket_slas', $joinId, "$joinId.ticket_id = tickets.id");
                    $qb->addGroupBy($selectId);
                    break;

                case TicketSearchParams::GROUP_AGENT:
                    $qb->addSelect("tickets.agent_id AS $selectId");
                    $qb->addGroupBy('tickets.agent_id');
                    break;

                case TicketSearchParams::GROUP_AGENT_TEAM:
                    $qb->addSelect("tickets.agent_team_id AS $selectId");
                    $qb->addGroupBy('tickets.agent_team_id');
                    break;

                case TicketSearchParams::GROUP_DEPARTMENT:
                    $qb->addSelect("tickets.department_id AS $selectId");
                    $qb->addGroupBy('tickets.department_id');
                    break;

                case TicketSearchParams::GROUP_WORKFLOW:
                    $qb->addSelect("tickets.workflow_id AS $selectId");
                    $qb->addGroupBy('tickets.workflow_id');
                    break;

                case TicketSearchParams::GROUP_PRIORITY:
                    $qb->addSelect("tickets.priority_id AS $selectId");
                    $qb->addGroupBy('tickets.priority_id');
                    break;

                case TicketSearchParams::GROUP_CATEGORY:
                    $qb->addSelect("tickets.category_id AS $selectId");
                    $qb->addGroupBy('tickets.category_id');
                    break;

                case TicketSearchParams::GROUP_PRODUCT:
                    $qb->addSelect("tickets.product_id AS $selectId");
                    $qb->addGroupBy('tickets.product_id');
                    break;

                case TicketSearchParams::GROUP_LANGUAGE:
                    $qb->addSelect("tickets.language_id AS $selectId");
                    $qb->addGroupBy('tickets.language_id');
                    break;

                case TicketSearchParams::GROUP_URGENCY:
                    $qb->addSelect("tickets.urgency AS $selectId");
                    $qb->addGroupBy('tickets.urgency');
                    break;

                case TicketSearchParams::GROUP_DATE_CREATED:
                    $ranges   = GroupingCounter::getTimeRanges();
                    $now      = time();
                    $sqlParts = [];

                    foreach (GroupingCounter::getTimeGroups() as $t) {
                        $from       = date('Y-m-d H:i:s', max($now - $t, 0));
                        $to         = date('Y-m-d H:i:s', $now - $ranges[$t]);
                        $sqlParts[] = " WHEN tickets.date_created BETWEEN '$from' AND '$to' THEN $t ";
                    }

                    $sql = 'CASE '.implode('', $sqlParts)." ELSE 0 END AS $selectId";

                    $qb->addSelect($sql);
                    $qb->addGroupBy($selectId);
                    break;

                case TicketSearchParams::GROUP_TICKET_FIELD_PREFIX:
                    $ticketFieldId = (int) $fieldInfo['name'];

                    /** @var CustomField $field */
                    $field = ListUtils::findByProp($this->customFieldSet->customTicketFields, 'field', $ticketFieldId);

                    if (!$field) {
                        throw new \InvalidArgumentException('Unknown grouping field: '.$fieldId);
                    }

                    if (!$field->isGroupingCapable()) {
                        throw new \InvalidArgumentException('Field is not a valid grouping field: '.$fieldId);
                    }

                    // All grouping fields are choice fields at the moment,
                    // so we group on the field_id which is the specific option selected
                    $qb->addSelect("COALESCE($joinId.field_id, 0) AS $selectId");
                    $qb->leftJoin('tickets', 'custom_data_ticket', $joinId, "$joinId.ticket_id = tickets.id AND $joinId.root_field_id = {$field->field}");
                    $qb->addGroupBy($selectId);

                    break;

                default:
                    throw new \InvalidArgumentException("Unknown grouping field: {$fieldId} (type: {$fieldInfo['type']})");
            }
        }
    }

    /**
     * @param SqlBuilder $qb
     * @param array      $groupFields
     */
    private function addSubFilterBy(SqlBuilder $qb, array $subFilterFields)
    {
        if (empty($subFilterFields)) {
            return;
        }

        foreach ($subFilterFields as $idx => $subFilterInfo) {
            list($fieldId, $value) = $subFilterInfo;

            $joinId  = 'subfilter'.$idx;
            $placeId = 'subfilterval'.$idx;

            switch ($fieldId) {
                case TicketSearchParams::GROUP_STATUS:
                    $qb->andWhere("tickets.status = :$placeId")->setParameter($placeId, $value);
                    break;

                case TicketSearchParams::GROUP_SLA_SEVERITY:
                    $qb->leftJoin('tickets', 'ticket_slas', $joinId, "$joinId.ticket_id = tickets.id");
                    $qb->andWhere("$joinId.sla_status = :$placeId")->setParameter($placeId, $value);
                    break;

                case TicketSearchParams::GROUP_AGENT:
                    $qb->andWhere("tickets.agent_id = :$placeId")->setParameter($placeId, $value);
                    break;

                case TicketSearchParams::GROUP_AGENT_TEAM:
                    $qb->andWhere("tickets.agent_team_id = :$placeId")->setParameter($placeId, $value);
                    break;

                case TicketSearchParams::GROUP_DEPARTMENT:
                    $qb->andWhere("tickets.department_id = :$placeId")->setParameter($placeId, $value);
                    break;

                case TicketSearchParams::GROUP_WORKFLOW:
                    $qb->andWhere("tickets.workflow_id = :$placeId")->setParameter($placeId, $value);
                    break;

                case TicketSearchParams::GROUP_PRIORITY:
                    $qb->andWhere("tickets.priority_id = :$placeId")->setParameter($placeId, $value);
                    break;

                case TicketSearchParams::GROUP_CATEGORY:
                    $qb->andWhere("tickets.category_id = :$placeId")->setParameter($placeId, $value);
                    break;

                case TicketSearchParams::GROUP_PRODUCT:
                    $qb->andWhere("tickets.product_id = :$placeId")->setParameter($placeId, $value);
                    break;

                case TicketSearchParams::GROUP_LANGUAGE:
                    $qb->andWhere("tickets.language_id = :$placeId")->setParameter($placeId, $value);
                    break;

                case TicketSearchParams::GROUP_DATE_CREATED:
                    $ranges = GroupingCounter::getTimeRanges();
                    if (!isset($ranges[$value])) {
                        $qb->andWhere('0');
                        break;
                    }

                    $now      = time();
                    $placeIdA = $placeId.'a';
                    $placeIdB = $placeId.'b';
                    $dateA    = date('Y-m-d H:i:s', max($now - $value, 0));
                    $dateB    = date('Y-m-d H:i:s', $now - $ranges[$value]);

                    $qb->andWhere("tickets.date_created BETWEEN :$placeIdA AND :$placeIdB")
                       ->setParameter($placeIdA, $dateA)
                       ->setParameter($placeIdB, $dateB)
                    ;
                    break;

                default:
                    list($fieldType, $customFieldId) = $this->customFieldSet->parseCustomFieldId($fieldId);

                    /** @var CustomField $field */
                    $field = null;

                    if ($customFieldId) {
                        switch ($fieldType) {
                            case 'ticket.data':
                                $field = ListUtils::first($this->customFieldSet->customTicketFields, StructComparer::byProp('field', $customFieldId));
                                break;
                            default:
                                $field = null;
                                break;
                        }
                    }

                    if (!$field || !$field->isGroupingCapable()) {
                        throw new \InvalidArgumentException('Unknown sub-filter field: '.$fieldId);
                    }

                    switch ($field->type) {
                        case CustomDefAbstract::TYPE_CHOICE:
                            if ($value === null || $value == -1) {
                                $qb->leftJoin('tickets', 'custom_data_ticket', $joinId, "$joinId.ticket_id = tickets.id AND $joinId.root_field_id = :rootFieldId")
                                    ->andWhere("$joinId.field_id IS NULL")
                                    ->setParameter('rootFieldId', $field->field);
                            } else {
                                if ($this->fieldOptionMapper) {
                                    $value = $this->fieldOptionMapper->getValueById(TermFieldIds::getCustomFieldTermId($fieldType, $field->field), $value);
                                }

                                $qb->leftJoin('tickets', 'custom_data_ticket', $joinId, "$joinId.ticket_id = tickets.id AND $joinId.root_field_id = :rootFieldId")
                                    ->andWhere("$joinId.field_id IN (:filterValue)")
                                    ->setParameter('rootFieldId', $field->field)
                                    ->setParameter('filterValue', (array) $value, Connection::PARAM_INT_ARRAY);
                            }
                            break;

                        default:
                            throw new \RuntimeException('Unhandled sub-filtering on custom field type '.$field->type);
                    }

                    break;
            }
        }
    }

    private function addOrderBy(SqlBuilder $qb, array $orderFields)
    {
        if (empty($orderFields)) {
            return;
        }

        foreach ($orderFields as $idx => $orderInfo) {
            list($fieldId, $order) = $orderInfo;

            $joinId = 'order'.$idx;

            switch ($fieldId) {
                case TicketSearchParams::ORDER_SLA_SEVERITY:
                    $qb->leftJoin('tickets', 'ticket_slas', $joinId, "$joinId.ticket_id = tickets.id");
                    $qb->addOrderBy("MAX(FIELD($joinId.sla_status, 'ok', 'warning', 'fail'))");
                    break;
                case TicketSearchParams::ORDER_ID:
                    $qb->addOrderBy('tickets.id', $order);
                    break;
                case TicketSearchParams::ORDER_URGENCY:
                    $qb->addOrderBy('tickets.urgency', $order);
                    break;
                case TicketSearchParams::ORDER_PRIORITY:
                    $qb->addOrderBy('tickets.priority', $order);
                    break;
                case TicketSearchParams::ORDER_DATE_CREATED:
                    $qb->addOrderBy('tickets.date_created', $order);
                    break;
                case TicketSearchParams::ORDER_DATE_RESOLVED:
                    $qb->addOrderBy('tickets.date_resolved', $order);
                    break;
                case TicketSearchParams::ORDER_DATE_ARCHIVED:
                    $qb->addOrderBy('tickets.date_archived', $order);
                    break;
                case TicketSearchParams::ORDER_DATE_LAST_USER_REPLY:
                    $qb->addOrderBy('tickets.date_last_user_reply', $order);
                    break;
                case TicketSearchParams::ORDER_DATE_LAST_AGENT_REPLY:
                    $qb->addOrderBy('tickets.date_last_agent_reply', $order);
                    break;
                case TicketSearchParams::ORDER_DATE_LAST_REPLY:
                    $qb->addOrderBy("
                        GREATEST(
                            COALESCE(tickets.date_last_agent_reply, '0000-00-00'),
                            COALESCE(tickets.date_last_user_reply, '0000-00-00'),
                            tickets.date_created
                        )
                    ");
                    break;
                case TicketSearchParams::ORDER_DATE_USER_WAITING:
                    $qb->addOrderBy('tickets.date_user_waiting', $order);
                    break;
                default:
                    throw new \InvalidArgumentException();
            }
        }
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

        if ($this->isFunctionTerm($term)) {
            $fnName = $term->options->value->name;

            if ($funcHandlers = $this->getHandlersForFunc($fnName)) {
                $isInvalid = false;
                foreach ($this->getHandlersForFunc($fnName) as $h) {
                    if ($h->getSqlHandlerDef()->canHandleFieldFunc($fnName, $fieldId, $operator)) {
                        return $h->buildQueryFuncCondition(
                            $h->getSqlHandlerDef()->getDefinedFuncName($fnName),
                            $fieldId,
                            $operator,
                            $this->getValueResolver()->getFuncCallParamValues($term->options->value, $term, $context),
                            $context,
                            $term
                        );
                    } else {
                        $isInvalid = true;
                    }
                }

                if ($isInvalid) {
                    throw new \InvalidArgumentException("Invalid function call: {$fnName} with field {$fieldId} and operator {$operator}.");
                }
            }
        }

        $fieldHandlers = $this->getHandlersForFieldId($fieldId);

        if (empty($fieldHandlers)) {
            throw new \OutOfBoundsException("No handler is capable of handling $fieldId");
        }

        $options = $this->getValueResolver()->optionValueFromTerm($term, $context);

        $parts = [];
        foreach ($fieldHandlers as  $handler) {
            /** @var $handler SqlTermHandlerInterface */
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

    /**
     * @return SqlTermHandlerInterface[]
     */
    private function getHandlersForFieldId($fieldId)
    {
        return ListUtils::filter($this->handlers, function (SqlTermHandlerInterface $h) use ($fieldId) {
            return $h->getSqlHandlerDef()->hasField($fieldId);
        });
    }

    /**
     * @return SqlTermHandlerInterface[]
     */
    private function getHandlersForFunc($func)
    {
        return ListUtils::filter($this->handlers, function (SqlTermHandlerInterface $h) use ($func) {
            return $h->getSqlHandlerDef()->hasFunction($func);
        });
    }
}
