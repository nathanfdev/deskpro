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

        if ($rootPart instanceof TermGroup) {
            return $this->doesTermGroupMatch($rootPart, $ticketModel, $context);
        } else {
            return $this->doesTermMatch($rootPart, $ticketModel, $context);
        }
    }

    /**
     * @param array        $termGroup
     * @param TicketModel  $ticketModel
     * @param AgentContext $agentContext
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
