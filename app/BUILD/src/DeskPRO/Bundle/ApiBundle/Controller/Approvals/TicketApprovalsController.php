<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Approvals;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\AbstractApprovalsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Approval\TicketApprovalType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\TicketsVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ApprovalTypesController
 *
 * @ApiModes("all")
 * @ApiUserContext("agent")
 * @ApiDoc(
 *     target="all",
 *     section="Approvals",
 *     output="DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval",
 *     input={
 *        "class"="DeskPRO\Bundle\AppBundle\Form\Type\Approval\TicketApprovalType",
 *        "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval",
 *        }
 *     },
 * )
 * @RequireAgentPermissions()
 */
class TicketApprovalsController extends AbstractApprovalsController
{
    public static $entity = TicketApproval::class;
    public static $type = TicketApprovalType::class;
    public static $listOrder = 'ASC';
    public static $listSort = 'id';
    public static $exposeOnly = [
        'list',
        'count',
        'approve',
        'reject',
        'cancel',
        'postApproval',
    ];

    /**
     * @ApiDoc(
     *      description="Create a new ticket approval",
     *      tags={"CRUD"="#ffa500"},
     *      statusCodes={
     *          201="Returned in case of successful resource creation",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Rest\Post("/tickets/{ticketId}/ticket_approvals", requirements={"ticketId"="\d+"})
     * @ParamConverter(name="ticket", options={"mapping"={"ticketId"="id"}})
     *
     * @param Ticket $ticket
     * @param Request $request
     *
     * @return View
     * @throws \Exception
     */
    public function postApprovalAction(Ticket $ticket, Request $request)
    {
        $this->checkExposed(__METHOD__);
        $this->denyAccessUnlessGranted(TicketsVoter::ADD_APPROVAL, new PermissionGroupContext($ticket));

        $form = $this->createForm(static::$type);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        /** @var TicketApproval $approval */
        $approval = $form->getData();
        $approval->setTicket($ticket);

        try {
            $this->getApprovalManager()->saveApproval(
                $approval,
                $this->createExecutionContext()
            );
        } catch (\DomainException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return View::create($this->wrap($approval), Response::HTTP_CREATED);
    }

    /**
     * @ApiDoc(
     *      description="Get collection of ticket approvals",
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
     * @Rest\Get("/tickets/{ticketId}/ticket_approvals", requirements={"ticketId"="\d+"})
     *
     * @param Request $request
     *
     * @return View
     * @throws \Exception
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
     * @Rest\Get("/tickets/{ticketId}/ticket_approvals/counts", requirements={"ticketId"="\d+"})
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
     * @Rest\Put("/ticket_approvals/{id}/cancel", requirements={"id"="\d+"})
     *
     * @param AbstractBaseApproval $approval
     *
     * @param Request $request
     * @return View
     * @throws \Exception
     */
    public function cancelAction(AbstractBaseApproval $approval, Request $request)
    {
        if (!($approval instanceof TicketApproval)) {
            throw new BadRequestHttpException('Approval must be of type '.TicketApproval::class);
        }

        $this->denyAccessUnlessGranted(TicketsVoter::CANCEL_APPROVAL, new PermissionGroupContext($approval->getTicket()));

        return parent::cancelAction($approval, $request);
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
     * @Rest\Post("/ticket_approvals/{id}/approve", requirements={"id"="\d+"})
     *
     * @param AbstractBaseApproval $approval
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     * @throws \Exception
     */
    public function approveAction(AbstractBaseApproval $approval, Request $request)
    {
        return parent::approveAction($approval, $request);
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
     * @Rest\Post("/ticket_approvals/{id}/reject", requirements={"id"="\d+"})
     *
     * @param AbstractBaseApproval $approval
     * @param Request $request
     * @return View
     * @throws \Exception
     */
    public function rejectAction(AbstractBaseApproval $approval, Request $request)
    {
        return parent::rejectAction($approval, $request);
    }

    /**
     * {@inheritDoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        if ($request->attributes->has('ticketId')) {
            $qb
                ->andWhere("IDENTITY({$alias}.ticket) = :ticketId")
                ->setParameter('ticketId', $request->attributes->getInt('ticketId'))
            ;
        }
    }
}
