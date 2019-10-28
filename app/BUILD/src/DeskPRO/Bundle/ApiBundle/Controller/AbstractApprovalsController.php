<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Security\Token\ApiKeySecurityToken;
use DeskPRO\Bundle\AppBundle\Approval\ApprovalManager;
use DeskPRO\Bundle\AppBundle\Approval\ExecutorContext;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Approval\ApprovalResponseType;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class AbstractApprovalsController.
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
     * @param Request              $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function cancelAction(AbstractBaseApproval $approval, Request $request)
    {
        try {
            $this->getApprovalManager()->cancelApproval(
                $approval,
                $this->createExecutionContext()
            );
        } catch (\DomainException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

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
     * @param Request              $request
     *
     * @throws \Exception
     *
     * @return \FOS\RestBundle\View\View
     */
    public function approveAction(AbstractBaseApproval $approval, Request $request)
    {
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
     * @param Request              $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function rejectAction(AbstractBaseApproval $approval, Request $request)
    {
        return $this->handleApprovalResponseRequest(
            $request,
            $approval,
            ApprovalResponse::createRejectionResponse($this->getUser())
        );
    }

    /**
     * @param Request              $request
     * @param AbstractBaseApproval $approval
     * @param ApprovalResponse     $approvalResponse
     *
     * @throws \Exception
     *
     * @return View
     */
    protected function handleApprovalResponseRequest(
        Request $request,
        AbstractBaseApproval $approval,
        ApprovalResponse $approvalResponse
    ) {
        if (!$approval->hasApprover($this->getUser())) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $form = $this->createForm(ApprovalResponseType::class, $approvalResponse);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        try {
            $this->getApprovalManager()->addApprovalResponse(
                $approval,
                $approvalResponse,
                $this->createExecutionContext()
            );
        } catch (\DomainException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return View::create($this->wrap($approvalResponse), Response::HTTP_CREATED);
    }

    /**
     * @throws \Exception
     *
     * @return ExecutorContext
     */
    protected function createExecutionContext()
    {
        return $this->getApprovalManager()->createContext(
            $this->getExecutorContextMethod(),
            $this->getUser()
        );
    }

    /**
     * @return string
     */
    protected function getExecutorContextMethod()
    {
        $isApi = ($this->get('security.token_storage')->getToken() instanceof ApiKeySecurityToken);

        return $isApi
            ? ExecutorContext::METHOD_API
            : ExecutorContext::METHOD_WEB
        ;
    }

    /**
     * @return ApprovalManager
     */
    protected function getApprovalManager()
    {
        return $this->get('approval.approval_manager');
    }
}
