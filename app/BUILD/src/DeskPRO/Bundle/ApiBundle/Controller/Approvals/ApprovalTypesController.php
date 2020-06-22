<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Approvals;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType;
use DeskPRO\Bundle\AppBundle\Form\Type\Approval\ApprovalTypeType;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ApprovalTypesController.
 *
 * @ApiModes("all")
 * @ApiUserContext("admin", agent={"list", "get", "count"})
 * @Rest\Route("/approval_types")
 * @ApiDoc(
 *     target="all",
 *     section="Approvals",
 *     output="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Approval\ApprovalTypeType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType",
 *      }
 *     }
 * )
 * @ApiDoc(
 *     target="listAction",
 *     description="get list of approval types",
 *     filters={
 *         {"name"="is_deleted", "pattern"="(1|0)", "description"="show only deleted approval types", "dataType"="boolean"}
 *     },
 *     statusCodes={
 *         200="OK"
 *     }
 * )
 * @ApiDoc(
 *     target="getAction",
 *     description="get approval type",
 *     filters={
 *         {"name"="is_deleted", "pattern"="(1|0)", "description"="show a deleted approval type", "dataType"="boolean"}
 *     },
 *     statusCodes={
 *         200="OK"
 *     }
 * )
 */
class ApprovalTypesController extends CrudController
{
    public static $entity       = ApprovalType::class;
    public static $type         = ApprovalTypeType::class;
    public static $listPaginate = true;
    public static $listOrder    = 'ASC';
    public static $listSort     = 'id';

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    protected function deleteEntity($entity)
    {
        try {
            parent::deleteEntity($entity);
        } catch (ForeignKeyConstraintViolationException $e) {
            throw new BadRequestHttpException('It is not possible to delete a Type while there are Templates '
                .'associated with it. Please delete or reassign the Templates, before deleting the Type.', $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        if ($request->query->getBoolean('is_deleted')) {
            $qb->andWhere("$alias.isDeleted = TRUE");
        } else {
            $qb->andWhere("$alias.isDeleted != TRUE");
        }
    }
}
