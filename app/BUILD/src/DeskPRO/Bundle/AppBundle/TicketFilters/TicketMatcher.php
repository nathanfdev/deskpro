<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Context\AgentContext;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\FunctionCompareDef;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TermsHandlerInterface;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\GroupOp\AndGroupOp;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\GroupOp\NotGroupOp;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\GroupOp\OrGroupOp;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\TermGroup;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\CompareOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\FuncVal;

/**
 * The matcher matches a ticket against in-memory model values.
 */
class TicketMatcher
{
    /**
     * @var ValueResolver
     */
    private $valueResovler;

    /**
     * @var TermsHandlerInterface[]
     */
    private $handlers;

    /**
     * FieldId => Handler.
     *
     * @var TermsHandlerInterface[]
     */
    private $fieldToHandler;

    /**
     * The match ID is a concat of the operator and function name and field id.
     *
     * @var matchId => [handler, def]
     */
    private $matchFunctionMap;

    /**
     * TicketMatcher constructor.
     *
     * @param ValueResolver           $valueResolver
     * @param TermsHandlerInterface[] $handlers
     */
    public function __construct(ValueResolver $valueResolver, array $handlers)
    {
        $this->valueResovler = $valueResolver;
        $this->handlers      = $handlers;

        $this->fieldToHandler   = [];
        $this->matchFunctionMap = [];

        foreach ($handlers as $h) {
            foreach ($h->getHandledFields() as $fid) {
                if (!isset($this->fieldToHandler[$fid])) {
                    $this->fieldToHandler[$fid] = [];
                }
                $this->fieldToHandler[$fid][] = $h;
            }

            foreach ($h->getCompareFunctions() as $def) {
                $name = $def->getIdName();
                foreach ($def->fields as $field) {
                    $id                          = $name.'--'.$field;
                    $this->matchFunctionMap[$id] = [$h, $def];
                }
            }
        }
    }

    /**
     * @param Query       $query
     * @param TicketModel $ticketModel
     * @param Context     $matcherContext
     *
     * @return bool
     */
    public function doesQueryMatch(Query $query, TicketModel $ticketModel, Context $matcherContext)
    {
        $rootPart = $query->root;

        if ($rootPart instanceof TermGroup) {
            return $this->doesTermGroupMatch($rootPart, $ticketModel, $matcherContext);
        } else {
            return $this->doesTermMatch($rootPart, $ticketModel, $matcherContext);
        }
    }

    /**
     * @param array        $termGroup
     * @param TicketModel  $ticketModel
     * @param AgentContext $agentContext
     *
     * @return bool
     */
    private function doesTermGroupMatch(TermGroup $termGroup, TicketModel $ticketModel, Context $matcherContext)
    {
        $op       = $termGroup->operator;
        $anyMatch = false;
        $anyFail  = false;

        foreach ($termGroup->terms as $term) {
            if ($term instanceof TermGroup) {
                if ($this->doesTermGroupMatch($term, $ticketModel, $matcherContext)) {
                    $anyMatch = true;
                } else {
                    $anyFail = true;
                }
            } else {
                if ($this->doesTermMatch($term, $ticketModel, $matcherContext)) {
                    $anyMatch = true;
                } else {
                    $anyFail = true;
                }
            }

            // possible return early
            if ($op instanceof OrGroupOp && $anyMatch) {
                return true;
            } elseif ($op instanceof AndGroupOp && $anyFail) {
                return false;
            }
        }

        if ($op instanceof OrGroupOp) {
            return $anyMatch;
        }

        if ($op instanceof AndGroupOp) {
            return $anyMatch && !$anyFail;
        }

        // not is actually interretted as NOT (AND)
        if ($op instanceof NotGroupOp) {
            return !($anyMatch && !$anyFail);
        }

        throw new \InvalidArgumentException('Unknown operator');
    }

    /**
     * @param array        $term
     * @param TicketModel  $ticketModel
     * @param AgentContext $agentContext
     */
    public function doesTermMatch(Term $term, TicketModel $ticketModel, Context $matcherContext)
    {
        $fieldId  = $term->field->identity;
        $operator = $term->operator->getOperator();

        // Top-level value is a function call,
        // see if we handle it with a special handler
        if ($term->options instanceof CompareOpt && $term->options->value instanceof FuncVal) {
            $name         = strtolower($term->options->value->name);
            $matchFuncId  = $name.'--'.$term->field->identity;
            $matchFuncAny = $name.'--*';

            $match = null;
            if (isset($this->matchFunctionMap[$matchFuncId])) {
                $match = $this->matchFunctionMap[$matchFuncId];
            } elseif (isset($this->matchFunctionMap[$matchFuncAny])) {
                $match = $this->matchFunctionMap[$matchFuncAny];
            }

            if ($match) {
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
                    $this->valueResovler->getFuncCallParamValues($term->options->value, $term, $matcherContext),
                    $ticketModel,
                    $matcherContext,
                    $term
                );
            }
        }

        if (empty($this->fieldToHandler[$fieldId])) {
            throw new \OutOfBoundsException("No handler is capable of handling $fieldId");
        }

        $options = $this->valueResovler->optionValueFromTerm($term, $matcherContext);

        foreach ($this->fieldToHandler[$fieldId] as $handler) {
            /** @var $handler TermsHandlerInterface */
            if ($handler->doesTicketMatch(
                $fieldId,
                $operator,
                $options,
                $ticketModel,
                $matcherContext,
                $term
            )) {
                return true;
            }
        }

        return false;
    }
}
