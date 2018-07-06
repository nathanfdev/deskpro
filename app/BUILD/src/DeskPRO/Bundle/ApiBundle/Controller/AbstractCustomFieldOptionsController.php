<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomFieldOptionType;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractCustomFieldOptionsController.
 */
abstract class AbstractCustomFieldOptionsController extends CrudSubController
{
    public static $parentProperty = 'parent';
    public static $type           = CustomFieldOptionType::class;

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        /** @var CustomDefAbstract $parent */
        $parent = $this->findParentOr404();
        if (!$parent->getParent()) {
            $prop  = static::$parentProperty;
            $param = static::$parentParameter;

            $qb->andWhere("$alias.options NOT LIKE '%parent_id%'");
            $qb->andWhere("$alias.$prop = :$param");
            $qb->setParameter($param, $this->findParentOr404()->getId());
        } else {
            $qb->andWhere("$alias.options LIKE '%\"parent_id\";i:{$parent->getId()};%'");
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'parent' => $this->findParentOr404(),
        ]);

        return parent::handleForm($model, $request, $options);
    }
}
