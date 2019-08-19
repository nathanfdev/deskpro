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
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PersonApprovalResponsesController
 *
 * @ApiModes("all")
 * @ApiUserContext("agent")
 * @ApiDoc(
 *     target="all",
 *     section="Approvals",
 *     output="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse",
 * )
 * @RequireAgentPermissions()
 */
class PersonApprovalResponsesController extends CrudController
{
    public static $entity = ApprovalResponse::class;
    public static $listOrder = 'ASC';
    public static $listSort = 'id';
    public static $exposeOnly = [
        'list',
        'count',
    ];

    /**
     * @ApiDoc(
     *      description="Get collection of resources",
     *      tags={"CRUD"="#ffa500"},
     *      filters={
     *          {"name"="page", "pattern"="\d", "description"="Which page to display", "dataType"="integer"},
     *          {"name"="count", "pattern"="\d", "description"="Resource per page count", "dataType"="integer"},
     *          {"name"="limit", "pattern"="\d", "description"="Max number of resources to return", "dataType"="integer"},
     *          {"name"="ids", "pattern"="[\d,]+", "description"="Comma separated list of IDs", "dataType"="string"},
     *      },
     *      statusCodes={
     *          200="Returned if your request was successful",
     *          400="An error will occur if you provide wrong filters set",
     *      }
     * )
     * @Rest\Get("/person/{id}/approval_responses", requirements={"id"="\d+"})
     *
     * @param Request $request
     *
     * @return View
     * @throws \Exception
     *
     */
    public function listAction(Request $request)
    {
        return parent::listAction($request);
    }

    /**
     * @ApiDoc(
     *      description="Count list",
     *      tags={"CRUD"="#ffa500"},
     *      statusCodes={
     *         200="Returned if successful request",
     *         400="Returned if you filter set was malformed"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     *
     * @Rest\Get("/person/{id}/approval_responses/counts", requirements={"id"="\d+"})
     *
     * @param Request $request
     *
     * @return View
     * @throws \Exception
     *
     */
    public function countAction(Request $request)
    {
        return parent::countAction($request);
    }

    /**
     * {@inheritDoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb
            ->addSelect('approval')
            ->join("{$alias}.approval", 'approval')
            ->andWhere("IDENTITY({$alias}.approver) = :approverId")
            ->setParameter('approverId', $request->attributes->getInt('id'))
        ;
    }
}
