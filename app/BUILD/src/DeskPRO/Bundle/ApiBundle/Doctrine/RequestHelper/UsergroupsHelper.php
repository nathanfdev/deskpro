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

namespace DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper;

/**
 * Class UsergroupsHelper.
 */
class UsergroupsHelper
{
    /**
     * @param RequestQueryContext $context
     */
    public static function joinUserGroups(RequestQueryContext $context)
    {
        $alias = $context->getAlias();
        $qb    = $context->getQb();

        if (in_array('ug', $qb->getAllAliases())) {
            return;
        }

        $qb->leftJoin("$alias.usergroups", 'ug');
        $qb->andWhere("ug.sys_name NOT IN ('everyone', 'registered') OR ug.sys_name is null");
    }

    /**
     * @param RequestQueryContext $context
     */
    public static function applyUsergroupsFilters(RequestQueryContext $context)
    {
        self::joinUserGroups($context);
        $qb = $context->getQb();

        $userGroups = $context->getRequest()->get('user_group');
        if (null !== $userGroups) {
            if (is_array($userGroups)) {
                $qb->andWhere('ug.id in (:user_group_id)');
                $qb->setParameter('user_group_id', $userGroups);
            } else {
                $userGroup = (int) $userGroups;
                if ($userGroup > 0) {
                    $qb->andWhere('ug.id = :user_group_id');
                    $qb->setParameter('user_group_id', $userGroup);
                } else {
                    $qb->andWhere('ug.id IS NULL');
                }
            }
        }
    }

    /**
     * @param RequestQueryContext $context
     */
    public static function applyUserGroupsGroupBy(RequestQueryContext $context)
    {
        self::joinUserGroups($context);

        $qb = $context->getQb();
        $qb
            ->addSelect('ug.title as title')
            ->addSelect('ug.id as group_name')
            ->groupBy('group_name')
        ;
    }
}
