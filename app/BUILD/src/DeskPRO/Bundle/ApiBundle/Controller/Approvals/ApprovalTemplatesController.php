<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Approvals;

use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Form\Type\Approval\ApprovalTemplateType;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApprovalTemplatesController
 *
 * @ApiModes("all")
 * @ApiUserContext("admin", agent={"list", "get", "count"})
 * @Rest\Route("/approval_templates")
 * @ApiDoc(
 *     target="all",
 *     section="Approvals",
 *     output="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Approval\ApprovalTemplateType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate",
 *      }
 *     }
 * )
 * @RequireAgentPermissions()
 */
class ApprovalTemplatesController extends CrudController
{
    public static $entity = ApprovalTemplate::class;
    public static $type = ApprovalTemplateType::class;
    public static $listPaginate = true;
    public static $listOrder = 'ASC';
    public static $listSort = 'id';

    /**
     * @ApiDoc(
     *      description="Get an approval template.",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the approval template",
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
     * @param int     $id
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
     * @ApiUserContext("agent")
     *
     * @Rest\Get("/counts")
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
    protected function persistModel($model, FormInterface $form = null)
    {
        $em = $this->getManager();

        if (!$em->contains($model) && $model instanceof ApprovalTemplate) {
            $model->addDefaultSendTicketApprovalEmailActions();
        }

        $em->persist($model);
        $em->flush();

        return $model;
    }
}
