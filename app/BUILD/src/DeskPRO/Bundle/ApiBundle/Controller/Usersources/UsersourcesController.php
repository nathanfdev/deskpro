<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Usersources;

use Application\DeskPRO\Entity\Usersource;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UsersourcesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/user_sources/{context}", requirements={"context": "(agent|user)"})
 * @ApiDoc(target="all", section="Usersources", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Usersource")
 * @ApiUserContext("open")
 */
class UsersourcesController extends CrudController
{
    public static $exposeOnly = ['get', 'list', 'count'];
    public static $entity     = Usersource::class;
    public static $listSort   = 'display_order';
    public static $listOrder  = 'asc';

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb->andWhere("$alias.type = :context");
        $qb->setParameter('context', $request->attributes->get('context'));

        if ($request->get('type') === 'callback') {
            $qb->andWhere("$alias.source_type IN (:callback_sources)");
            $qb->setParameter('callback_sources', Usersource::$callbackAdapters);
        }

        $isEnabled = $request->get('is_enabled', true);
        if (null !== $isEnabled) {
            $qb->andWhere("$alias.is_enabled = :is_enabled");
            $qb->setParameter('is_enabled', $isEnabled);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function denyAccessUnlessGranted($attributes, $object = null, $message = 'Access Denied.')
    {
        // open endpoint, skip security checks
    }
}
