<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Approvals;

use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Form\Type\Approval\ApprovalTypeType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ApprovalTypesController
 *
 * @ApiModes("all")
 * @ApiUserContext("admin")
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
 * @RequireAgentPermissions()
 */
class ApprovalTypesController extends CrudController
{
    public static $entity = ApprovalType::class;
    public static $type = ApprovalTypeType::class;
    public static $listPaginate = true;
    public static $listOrder = 'ASC';
    public static $listSort = 'id';

    /**
     * @ApiDoc(
     *      description="Get an approval type.",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the approval type",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="We will return such status in case we found your entity",
     *          404="Not Found error will returned in case we can't find entity with specified ID"
     *      }
     * )
     * @ApiUserContext("agent")
     * @Rest\Get("/{id}", requirements={"id"="\d+"})
     *
     * @param Request $request
     * @param int $id
     *
     * @return View
     */
    public function getAction(Request $request, $id)
    {
        return parent::getAction($request, $id);
    }

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
     * @ApiUserContext("agent")
     * @Rest\Get("")
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
     * @ApiUserContext("agent")
     *
     * @Rest\Get("/counts")
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
        if ($request->query->getBoolean('is_deleted')) {
            $qb->andWhere("$alias.isDeleted = TRUE");
        } else {
            $qb->andWhere("$alias.isDeleted != TRUE");
        }
    }
}
