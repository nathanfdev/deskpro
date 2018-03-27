<?php

namespace DeskPRO\Bundle\AuditBundle\Entity\NamingStrategy;

use DeskPRO\Bundle\AuditBundle\Log\AuditLog;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class ExpressionNamingStrategy.
 */
class ExpressionNamingStartegy implements NamingStrategyInterface
{
    /**
     * @var ExpressionLanguage
     */
    private $language;

    /**
     * @var string
     */
    private $expression;

    /**
     * ExpressionNamingStrategy constructor.
     *
     * @param string $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
        $this->language   = new ExpressionLanguage();
    }

    /**
     * @param          $object
     * @param AuditLog $log
     *
     * @return string
     */
    public function getName($object, AuditLog $log)
    {
        return $this->language->evaluate($this->expression, ['object' => $object, 'log' => $log]);
    }
}
