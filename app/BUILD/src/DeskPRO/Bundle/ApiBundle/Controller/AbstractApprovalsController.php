<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Approval\ApprovalResponseType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class AbstractApprovalsController
 *
 * @package DeskPRO\Bundle\ApiBundle\Controller
 */
abstract class AbstractApprovalsController extends CrudController
{
    /**
     * @ApiDoc(
     *      description="Cancel an approval",
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
     *          204="Approval was cancelled",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     *
     * @param AbstractBaseApproval $approval
     * @param Request $request
     *
     * @return View
     * @throws \Exception
     */
    public function cancelAction(AbstractBaseApproval $approval)
    {
        try {
            $approval->cancel();
        } catch (\DomainException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        $this->persistModel($approval);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *      description="Approve an approval",
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
     *          201="Returned in case of successful resource created",
     *          400="We will return this in case your request was malformed",
     *      },
     *     output="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse",
     *     input={
     *        "class"="DeskPRO\Bundle\AppBundle\Form\Type\Approval\ApprovalResponseType",
     *        "options"={
     *          "data"="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse",
     *        }
     *     },
     * )
     *
     * @param AbstractBaseApproval $approval
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     * @throws \Exception
     */
    public function approveAction(AbstractBaseApproval $approval, Request $request)
    {
        $this->checkExposed(__METHOD__);

        return $this->handleApprovalResponseRequest(
            $request,
            $approval,
            ApprovalResponse::createApprovalResponse($this->getUser())
        );
    }

    /**
     * @ApiDoc(
     *      description="Reject an approval",
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
     *          201="Returned in case of successful resource created",
     *          400="We will return this in case your request was malformed",
     *      },
     *     output="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse",
     *     input={
     *        "class"="DeskPRO\Bundle\AppBundle\Form\Type\Approval\ApprovalResponseType",
     *        "options"={
     *          "data"="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse",
     *        }
     *     },
     * )
     *
     * @param AbstractBaseApproval $approval
     * @param Request $request
     * @return View
     * @throws \Exception
     */
    public function rejectAction(AbstractBaseApproval $approval, Request $request)
    {
        $this->checkExposed(__METHOD__);

        return $this->handleApprovalResponseRequest(
            $request,
            $approval,
            ApprovalResponse::createRejectionResponse($this->getUser())
        );
    }

    /**
     * @param Request $request
     * @param AbstractBaseApproval $approval
     * @param ApprovalResponse $approvalResponse
     * @return View
     * @throws \Exception
     */
    protected function handleApprovalResponseRequest(
        Request $request,
        AbstractBaseApproval $approval,
        ApprovalResponse $approvalResponse
    ) {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::CREATE, $this->getPermissionGroupContext($request));

        $form = $this->createForm(ApprovalResponseType::class, $approvalResponse);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        try {
            $approval->addResponse($approvalResponse);
        } catch (\DomainException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        $this->persistModel($approval);

        return View::create($this->wrap($approvalResponse), Response::HTTP_CREATED);
    }
}
