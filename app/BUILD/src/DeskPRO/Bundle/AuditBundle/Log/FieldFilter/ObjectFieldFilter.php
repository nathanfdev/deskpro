<?php

namespace DeskPRO\Bundle\AuditBundle\Log\FieldFilter;

use Symfony\Component\ExpressionLanguage\Expression;

/**
 * Class ObjectFieldFilter.
 */
class ObjectFieldFilter extends EntityFieldFilter
{
    /**
     * {@inheritdoc}
     */
    public function filter($value, $expression = '')
    {
        if (!$expression) {
            throw new \InvalidArgumentException('You have to provide expression in ObjectFieldFilter');
        }

        return parent::filter($value, $expression);
    }

    /**
     * {@inheritdoc}
     */
    public function doFilter($value, $expression)
    {
        if (is_object($value)) {
            return $this->language->evaluate(new Expression($expression), ['object' => $value]);
        }

        return $value;
    }
}
