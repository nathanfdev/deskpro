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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CrudSubController.
 *
 * Base REST CRUD controller for nested resources such as /people/1/labels. All children must declare parent
 * entity id URL param as {parentId}
 */
abstract class CrudSubController extends CrudController
{
    protected static $parentParameter = 'parentId';
    public static $parentProperty;

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $prop  = static::$parentProperty;
        $param = static::$parentParameter;
        $qb
            ->andWhere("$alias.$prop = :$param")
            ->setParameter($param, $this->findParentOr404()->getId());
    }

    /**
     * {@inheritdoc}
     */
    protected function instantiateEntity(Request $request)
    {
        $entity             = new static::$entity();
        $reflectionProperty = new \ReflectionProperty(static::$entity, static::$parentProperty);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($entity, $this->findParentOr404());

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        $parent             = $this->findParentOr404();
        $entity             = $this->findOr404(static::$entity, $id);
        $reflectionProperty = new \ReflectionProperty(static::$entity, static::$parentProperty);
        $reflectionProperty->setAccessible(true);
        $entityParent = $reflectionProperty->getValue($entity);
        if ($parent !== $entityParent) {
            throw $this->createNotFoundException('Requested resources does not belong to the specified parent');
        }

        return $entity;
    }

    /**
     * @return object
     */
    protected function findParentOr404()
    {
        $request     = $this->container->get('request_stack')->getCurrentRequest();
        $parentId    = $request->get(static::$parentParameter);
        $parentClass = $this
                           ->getManager()
                           ->getClassMetadata(trim(static::$entity, '\\'))
                           ->getAssociationMapping(static::$parentProperty)['targetEntity'];

        return $this->findOr404($parentClass, $parentId);
    }

    /**
     * {@inheritdoc}
     */
    protected function getLocationUrl($entity, Request $request, array $params = [])
    {
        return parent::getLocationUrl($entity, $request, [
            static::$parentParameter => $this->findParentOr404()->getId(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    protected function getPermissionGroupContext(Request $request)
    {
        return new PermissionGroupContext($this->findParentOr404(), static::$entity);
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @return object
     */
    protected function getPermissionGroupEntityContext($id, Request $request)
    {
        return new PermissionGroupContext($this->findParentOr404(), $this->findEntity($id, $request));
    }
}
