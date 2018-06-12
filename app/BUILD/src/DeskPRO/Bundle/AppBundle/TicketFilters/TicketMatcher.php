<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\ValueTermHandler;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\GroupOp\AndGroupOp;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\GroupOp\NotGroupOp;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\GroupOp\OrGroupOp;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\TermGroup;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\Util\ListUtils;
use Zend\Memory\Value;

/**
 * The matcher matches a ticket against in-memory model values.
 */
class TicketMatcher extends AbstractMatcher
{
    /**
     * @var ValueTermHandler[]
     */
    private $handlers;

    /**
     * TicketMatcher constructor.
     *
     * @param ValueResolver      $valueResolver
     * @param ValueTermHandler[] $handlers
     */
    public function __construct(ValueResolver $valueResolver, array $handlers)
    {
        parent::__construct($valueResolver);
        $this->handlers = $handlers;
    }

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

        if ($this->isFunctionTerm($term)) {
            $fnName = $term->options->value->name;

            if ($funcHandlers = $this->getHandlersForFunc($fnName)) {
                $isInvalid = false;
                foreach ($funcHandlers as $h) {
                    if ($h->getValueHandlerDef()->canHandleFieldFunc($fnName, $fieldId, $operator)) {
                        return $h->doesTicketMatchFunc(
                            $h->getValueHandlerDef()->getDefinedFuncName($fnName),
                            $fieldId,
                            $operator,
                            $this->getValueResovler()->getFuncCallParamValues($term->options->value, $term, $context),
                            $ticketModel,
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

    /**
     * @return ValueTermHandler[]
     */
    private function getHandlersForFieldId($fieldId)
    {
        return ListUtils::filter($this->handlers, function (ValueTermHandler $h) use ($fieldId) {
            return $h->getValueHandlerDef()->hasField($fieldId);
        });
    }

    /**
     * @return ValueTermHandler[]
     */
    private function getHandlersForFunc($func)
    {
        return ListUtils::filter($this->handlers, function (ValueTermHandler $h) use ($func) {
            return $h->getValueHandlerDef()->hasFunction($func);
        });
    }
}
