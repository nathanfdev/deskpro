<?php

namespace DeskPRO\Bundle\AuditBundle\Configuration;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression;

/**
 * Class Condition.
 */
class Condition
{
    /**
     * @var array
     */
    private $preconditions;

    /**
     * @var Expression|ParsedExpression
     */
    private $expression;

    /**
     * @var array
     */
    private $variables;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * Condition constructor.
     *
     * @param array              $preconditions
     * @param string             $expression
     * @param array              $variables
     * @param ExpressionLanguage $language
     */
    public function __construct(
        array $preconditions,
        $expression = '',
        array $variables = [],
        ExpressionLanguage $language = null
    ) {
        $this->preconditions = $preconditions;
        $this->expression    = $expression;
        $this->variables     = $variables;
        $this->language      = $language;
    }

    /**
     * @param AuditContext $context
     */
    private function init(AuditContext $context)
    {
        $entity    = $context->getEntity();
        $action    = $context->getAction();
        $changeSet = array_keys($context->getChangeSet());
        $performer = $context->getPerformer();

        if (!$this->initialized) {
            $this->expression = new Expression($this->expression);
            $names            = [];
            foreach ($this->variables as $variable) {
                $names[$variable] = $$variable;
            }
            $this->variables   = $names;
            $this->expression  = $this->language->parse($this->expression, array_keys($names));
            $this->initialized = true;
        }
    }

    /**
     * @param AuditContext $context
     *
     * @return bool
     */
    public function getBool(AuditContext $context)
    {
        if (!$this->checkPreconditions(array_keys($context->getChangeSet()))) {
            return false;
        }

        if ($this->expression) {
            $this->init($context);
        } else {
            return true; // this means that we have no expression, so condition contains only simple check about changeset
        }

        return $this->language->evaluate($this->expression, $this->variables);
    }

    /**
     * @param array $changeSet
     *
     * @return bool
     */
    private function checkPreconditions($changeSet)
    {
        if ($this->preconditions) {
            return array_reduce(
                $this->preconditions,
                function ($carry, $item) use ($changeSet) {
                    return $carry || in_array($item, $changeSet);
                },
                false
            );
        }

        return true;
    }
}
