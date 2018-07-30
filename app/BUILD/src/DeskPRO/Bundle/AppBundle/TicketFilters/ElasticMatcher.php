<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\ElasticTermHandlerInterface;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Node;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\TermGroup;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\Util\ListUtils;
use Elastica\Query as ElasticaQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Elastica\Query\Term as ElasticaTerm;
use Elastica\Query\Terms as ElasticaTerms;
use Elastica\SearchableInterface;

/**
 * Class ElasticMatcher.
 */
class ElasticMatcher extends AbstractMatcher
{
    /**
     * @var SearchableInterface
     */
    private $ticketSearch;

    /**
     * @var ElasticTermHandlerInterface[]
     */
    private $handlers;

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
     * Constructor.
     *
     * @param ValueResolver                 $valueResolver
     * @param SearchableInterface           $ticketSearch
     * @param ElasticTermHandlerInterface[] $handlers
     * @param CustomFieldSet                $customFieldSet
     * @param OptionMapperInterface         $fieldOptionMapper
     */
    public function __construct(
        ValueResolver         $valueResolver,
        SearchableInterface   $ticketSearch,
        array                 $handlers,
        CustomFieldSet        $customFieldSet = null,
        OptionMapperInterface $fieldOptionMapper = null
    ) {
        parent::__construct($valueResolver);

        $this->ticketSearch      = $ticketSearch;
        $this->handlers          = $handlers;
        $this->customFieldSet    = $customFieldSet;
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

    public function getCount(Query $query, Context $context, TicketSearchParams $params = null)
    {
        $search      = $this->ticketSearch->createSearch();
        $searchQuery = $this->buildQuery($query, $context);
        $response    = $search->search($searchQuery);

        return $response->count();
    }

    /**
     * @param Query              $query
     * @param Context            $context
     * @param TicketSearchParams $params
     *
     * @return array
     */
    public function getIds(Query $query, Context $context, TicketSearchParams $params = null)
    {
        $search      = $this->ticketSearch->createSearch();
        $searchQuery = $this->buildQuery($query, $context);

        $response = $search->search($searchQuery);
        $results  = $response->getResults();

        $ids = [];
        foreach ($results as $result) {
            $ids[] = $result->getId();
        }

        return $ids;
    }

    /**
     * @param Query   $query
     * @param Context $context
     *
     * @return ElasticaQuery
     */
    public function buildQuery(Query $query, Context $context)
    {
        $terms = $this->buildTermGroup($context, $query->root);

        if ($this->applyContextPermissions) {
            $permissionQuery = $this->buildPermissionConditionForAgent($context->getAgent());
            if ($permissionQuery) {
                $terms->addMust($permissionQuery);
            }
        }

        $elasticQuery = new ElasticaQuery();
        $elasticQuery->setQuery($terms);
        $elasticQuery->setSize(10000);

        return $elasticQuery;
    }

    /**
     * @param Context $context
     * @param Node    $termGroup
     *
     * @return BoolQuery
     */
    private function buildTermGroup(Context $context, Node $termGroup = null)
    {
        $query = new BoolQuery();
        if ($termGroup instanceof TermGroup) {
            foreach ($termGroup->terms as $op => $term) {
                if ($term instanceof TermGroup) {
                    $query->addMust($subGroup = $this->buildTermGroup($term, $context));
                } else {
                    $query->addMust($this->buildTerm($term, $context));
                }
            }
        } elseif ($termGroup) {
            $query->addMust($this->buildTerm($termGroup, $context));
        }

        return $query;
    }

    /**
     * @param Term    $term
     * @param Context $context
     *
     * @throws \Exception
     *
     * @return BoolQuery
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
                    if ($h->getElasticHandlerDef()->canHandleFieldFunc($fnName, $fieldId, $operator)) {
                        return $h->buildElasticFuncCondition(
                            $h->getElasticHandlerDef()->getDefinedFuncName($fnName),
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
            /** @var ElasticTermHandlerInterface $handler */
            $c = $handler->buildElasticCondition(
                $fieldId,
                $operator,
                $options,
                $context,
                $term
            );
            if ($c) {
                $parts[] = $c;
            }
        }

        if (empty($parts)) {
            return;
        }

        if (count($parts) === 1) {
            return $parts[0];
        }

        $group = new BoolQuery();
        foreach ($parts as $part) {
            $group->addMust($part);
        }

        return $group;
    }

    /**
     * @param Agent $agent
     *
     * @return BoolQuery|null
     */
    private function buildPermissionConditionForAgent(Agent $agent)
    {
        // can view everything, no perms to apply
        if ($agent->canViewAll()) {
            return null;
        }

        // Agent can view IF....
        $condGroup = new BoolQuery();

        // the ticket IS ASSIGNED to the agent or their teams
        $assignedPerms = new BoolQuery();
        $assignedPerms->addShould(new ElasticaTerm(['agent' => $agent->id]));

        if (!empty($agent->teams)) {
            $assignedPerms->addShould(new ElasticaTerms('agent_team', $agent->teams));
        }

        $condGroup->addShould($assignedPerms);

        // OR the ticket is in a dep i can see (and its a state that i can see)
        if (!empty($agent->allowed_departments) || $agent->all_departments_allowed) {
            $depCond = new BoolQuery();

            // if we need to be explicit, then we need to check specific ids
            if (!$agent->all_departments_allowed) {
                $depCond->addMust(new ElasticaTerms('department', $agent->allowed_departments));
            }

            if (!$agent->view_unassigned) {
                $notUnassignedTerm = new BoolQuery();
                $notUnassignedTerm->addShould(new Exists('agent'));
                $notUnassignedTerm->addShould(new Exists('agent_team'));

                // but only if its not unassigned
                $depCond->addMust($notUnassignedTerm);
            }

            if (!$agent->view_assigned) {
                // but only if its not assigned
                $depCond->addMustNot(new Exists('agent'));
                $depCond->addMustNot(new Exists('agent_team'));
            }

            $condGroup->addShould($depCond);
        }

        return $condGroup;
    }

    /**
     * @param string $fieldId
     *
     * @return ElasticTermHandlerInterface[]
     */
    private function getHandlersForFieldId($fieldId)
    {
        return ListUtils::filter($this->handlers, function (ElasticTermHandlerInterface $h) use ($fieldId) {
            return $h->getElasticHandlerDef()->hasField($fieldId);
        });
    }

    /**
     * @param string $func
     *
     * @return ElasticTermHandlerInterface[]
     */
    private function getHandlersForFunc($func)
    {
        return ListUtils::filter($this->handlers, function (ElasticTermHandlerInterface $h) use ($func) {
            return $h->getElasticHandlerDef()->hasFunction($func);
        });
    }
}
