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

namespace DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usergroup;
use Doctrine\ORM\Query\Expr\From;

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
        $qb  = $context->getQb();
        $ids = $context->getRequest()->get('user_group');

        if ((int) $ids === -1) {
            self::joinUserGroups($context);
            $qb->andWhere('ug.id IS NULL');
        } elseif (null !== $ids) {
            $ids = array_map('intval', (array) $context->getRequest()->get('user_group'));

            // filter "everyone" and "registered" usergroups
            // because they don't have records in `person2usergroups` and `organization2usergroups`

            $userGroups  = $qb->getEntityManager()->getRepository(Usergroup::class)->findBy(['id' => $ids]);
            $filteredUgs = [];
            $registered  = null;

            foreach ($userGroups as $userGroup) {
                if (!in_array($userGroup->getSysName(), [Usergroup::EVERYONE, Usergroup::REGISTERED])) {
                    $filteredUgs[] = $userGroup;
                } elseif ($userGroup->getSysName() === Usergroup::REGISTERED) {
                    $registered = $userGroup;
                }
            }

            if (!empty($filteredUgs)) {
                self::joinUserGroups($context);

                $qb->andWhere('ug.id in (:user_group_id)');
                $qb->setParameter('user_group_id', $filteredUgs);
            }

            /** @var From $fromDql */
            $fromDql = $qb->getDQLPart('from')[0];

            if ($registered && $fromDql->getFrom() === Person::class) {
                $qb->andWhere("{$context->getAlias()}.is_user = 1");
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
