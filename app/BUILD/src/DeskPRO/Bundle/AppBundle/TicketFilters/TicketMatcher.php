<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\FunctionCompareDef;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\GroupOp\AndGroupOp;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\GroupOp\NotGroupOp;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\GroupOp\OrGroupOp;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\TermGroup;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;

/**
 * The matcher matches a ticket against in-memory model values.
 */
class TicketMatcher extends AbstractMatcher
{
    /**
     * @param Query       $query
     * @param TicketModel $ticketModel
     * @param Context     $context
     *
     * @return bool
     */
    public function doesQueryMatch(Query $query, TicketModel $ticketModel, Context $context)
    {
        $rootPart = $query->root;

        if ($rootPart === null) {
            return true;
        }

        if ($rootPart instanceof TermGroup) {
            return $this->doesTermGroupMatch($rootPart, $ticketModel, $context);
        } else {
            return $this->doesTermMatch($rootPart, $ticketModel, $context);
        }
    }

    /**
     * @param array       $termGroup
     * @param TicketModel $ticketModel
     * @param Context     $context
     *
     * @return bool
     */
    private function doesTermGroupMatch(TermGroup $termGroup, TicketModel $ticketModel, Context $context)
    {
        $op       = $termGroup->operator;
        $anyMatch = false;
        $anyFail  = false;

        foreach ($termGroup->terms as $term) {
            if ($term instanceof TermGroup) {
                if ($this->doesTermGroupMatch($term, $ticketModel, $context)) {
                    $anyMatch = true;
                } else {
                    $anyFail = true;
                }
            } else {
                if ($this->doesTermMatch($term, $ticketModel, $context)) {
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
    public function doesTermMatch(Term $term, TicketModel $ticketModel, Context $context)
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
                $ticketModel,
                $context,
                $term
            );
        }

        $fieldHandlers = $this->getHandlersForFieldId($fieldId);

        if (empty($fieldHandlers)) {
            throw new \OutOfBoundsException("No handler is capable of handling $fieldId");
        }

        $options = $this->getValueResovler()->optionValueFromTerm($term, $context);

        foreach ($fieldHandlers as  $handler) {
            /** @var $handler TermsHandlerInterface */
            if ($handler->doesTicketMatch(
                $fieldId,
                $operator,
                $options,
                $ticketModel,
                $context,
                $term
            )) {
                return true;
            }
        }

        return false;
    }
}
