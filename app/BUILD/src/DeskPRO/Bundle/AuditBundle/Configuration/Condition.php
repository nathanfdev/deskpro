<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
     * @param string             $expression
     * @param array              $variables
     * @param ExpressionLanguage $language
     */
    public function __construct($expression, $variables, $language)
    {
        $this->expression = $expression;
        $this->variables  = $variables;
        $this->language   = $language;
    }

    /**
     * @param AuditContext $context
     */
    public function init(AuditContext $context)
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
     *
     */
    public function getBool()
    {
        if (!$this->initialized || !$this->expression instanceof ParsedExpression) {
            throw new \RuntimeException('You can\'t get condition result until init');
        }

        return $this->language->evaluate($this->expression, $this->variables);
    }
}
