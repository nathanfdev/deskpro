<?php

namespace DeskPRO\Bundle\AuditBundle\Log\FieldFilter;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class EntityFieldFilter.
 */
class EntityFieldFilter implements FieldFilterInterface
{
    /**
     * @var ExpressionLanguage
     */
    protected $language;

    /**
     * CollectionFieldFilter constructor.
     */
    public function __construct()
    {
        $this->language = new ExpressionLanguage();
    }

    /**
     * {@inheritdoc}
     */
    public function filter($value, $expression = 'entity.getId()')
    {
        return $this->doFilter($value, $expression);
    }

    /**
     * @param $value
     * @param $expression
     *
     * @return string
     */
    protected function doFilter($value, $expression)
    {
        if ($value instanceof EntityInterface || ($value instanceof DomainObject && (method_exists($value, 'getId') || isset($value['id'])))) {
            $value = $this->language->evaluate(new Expression($expression), ['entity' => $value]);
        }

        return $value;
    }
}
