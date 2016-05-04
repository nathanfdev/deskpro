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

namespace DeskPRO\Bundle\ApiBundle\Controller\Usersources;

use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\Adapter as UsersourceAdapter;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use deskpro_us_jwt\Usersource\Adapter\Jwt as JwtAdapter;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UsersourcesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/user_sources/{context}", requirements={"context": "(agent|user)"})
 */
class UsersourcesController extends CrudController
{
    public static $exposeOnly = ['get', 'list', 'count'];
    public static $entity     = Usersource::class;

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb->andWhere("$alias.type = :context");
        $qb->setParameter('context', $request->attributes->get('context'));

        if ($request->get('type') === 'callback') {
            $qb->andWhere("$alias.source_type IN (:callback_sources)");
            $qb->setParameter('callback_sources', [
                JwtAdapter::class,
                UsersourceAdapter\Facebook::class,
                UsersourceAdapter\Google::class,
                UsersourceAdapter\GooglePlus::class,
                UsersourceAdapter\Twitter::class,
                UsersourceAdapter\Saml::class,
            ]);
        }

        $isEnabled = $request->get('is_enabled', true);
        if (null !== $isEnabled) {
            $qb->andWhere("$alias.is_enabled = :is_enabled");
            $qb->setParameter('is_enabled', $isEnabled);
        }
    }
}
