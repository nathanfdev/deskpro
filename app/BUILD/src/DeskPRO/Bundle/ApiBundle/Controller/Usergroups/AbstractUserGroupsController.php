<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Usergroups;

use Application\DeskPRO\Entity\Usergroup;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractUserGroupsController.
 */
abstract class AbstractUserGroupsController extends CrudController
{
    /**
     * @var bool
     */
    public static $isAgentGroup;

    public static $exposeOnly = ['get', 'list', 'count'];
    public static $entity     = Usergroup::class;
    public static $listOrder  = 'asc';

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb->andWhere("$alias.is_enabled = 1");
        $qb->andWhere("$alias.is_agent_group = :is_agent_group");
        $qb->setParameter('is_agent_group', static::$isAgentGroup);
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        /** @var Usergroup $entity */
        $entity = parent::findEntity($id, $request);
        if (!$entity->isEnabled() || $entity->isAgentGroup() !== static::$isAgentGroup) {
            throw $this->createNotFoundException();
        }

        return $entity;
    }
}
