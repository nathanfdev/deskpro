<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Approvals;

use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Form\Type\Approval\ApprovalTypeType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * @ApiDoc(
     *      description="Delete a resource",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Returned if everything is ok and there is no such resource anymore",
     *          404="Well, looks like either resource already deleted either it doesn't exists at all"
     *      }
     * )
     * @Rest\Delete("/{id}", requirements={"id"="\d+"})
     *
     * @param int     $id
     * @param Request $request
     *
     * @return View
     */
    public function deleteAction($id, Request $request)
    {
        $this->checkExposed(__METHOD__);
        $this->denyAccessUnlessGranted(PermissionGroupVoter::DELETE, $this->getPermissionGroupEntityContext($id, $request));

        $entity = $this->findEntity($id, $request);

        try {
            $this->deleteEntity($entity);
        } catch (ForeignKeyConstraintViolationException $e) {
            throw new BadRequestHttpException('It is not possible to delete a Type while there are Templates '
                .'associated with it. Please delete or reassign the Templates, before deleting the Type.', $e);
        }

        return View::create([], Response::HTTP_OK);
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
