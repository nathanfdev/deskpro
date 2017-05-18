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
