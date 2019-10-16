<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Approvals;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PersonApprovalResponsesController.
 *
 * @ApiModes("all")
 * @ApiUserContext("agent")
 * @Rest\Route("/person/{personId}/approval_responses", requirements={"personId"="\d+"})
 * @ApiDoc(
 *     target="all",
 *     section="Approvals",
 *     output="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse",
 * )
 * @RequireAgentPermissions()
 */
class PersonApprovalResponsesController extends CrudController
{
    public static $entity     = ApprovalResponse::class;
    public static $listOrder  = 'ASC';
    public static $listSort   = 'id';
    public static $exposeOnly = ['list', 'count'];

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb
            ->addSelect('approval')
            ->join("{$alias}.approval", 'approval')
            ->andWhere("IDENTITY({$alias}.approver) = :approverId")
            ->setParameter('approverId', $request->attributes->getInt('personId'))
        ;
    }
}
