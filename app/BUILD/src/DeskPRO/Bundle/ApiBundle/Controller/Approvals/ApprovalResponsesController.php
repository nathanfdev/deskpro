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
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
/**
 * Class ApprovalTypesController
 *
 * @ApiModes("all")
 * @ApiUserContext("agent")
 * @Rest\Route("/approval_responses")
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
    public static $entity = ApprovalResponse::class;
    public static $type = ApprovalResponseType::class;
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
     * @Rest\Get("/{approvalId}", requirements={"approvalId"="\d+"})
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
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
     * @Rest\Get("/{approvalId}/counts", requirements={"approvalId"="\d+"})
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
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
            ->andWhere("IDENTITY({$alias}.approval) = :approvalId")
            ->setParameter('approvalId', $request->attributes->getInt('approvalId'))
        ;
    }
}
