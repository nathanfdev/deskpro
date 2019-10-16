<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Approvals;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;
use DeskPRO\Bundle\AppBundle\Form\Type\Approval\ApprovalResponseType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApprovalTypesController.
 *
 * @ApiModes("all")
 * @ApiUserContext("agent")
 * @Rest\Route("/approval_responses/{approvalId}")
 * @ApiDoc(
 *     target="all",
 *     section="Approvals",
 *     output="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse",
 *     input={
 *        "class"="DeskPRO\Bundle\AppBundle\Form\Type\Approval\ApprovalResponseType",
 *        "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse",
 *        }
 *     },
 * )
 * @RequireAgentPermissions()
 */
class ApprovalResponsesController extends CrudController
{
    public static $entity     = ApprovalResponse::class;
    public static $type       = ApprovalResponseType::class;
    public static $listOrder  = 'ASC';
    public static $listSort   = 'id';
    public static $exposeOnly = ['list', 'count'];

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb
            ->andWhere("IDENTITY({$alias}.approval) = :approvalId")
            ->setParameter('approvalId', $request->attributes->getInt('approvalId'))
        ;
    }
}
