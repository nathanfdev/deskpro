<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use DeskPRO\Bundle\AppBundle\Approval\ExecutorContext;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\ApprovalResponseType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TicketApprovalsController
 *
 * @package DeskPRO\Bundle\PortalBundle\Controller
 */
class TicketApprovalsController extends AbstractController
{
    /**
     * Number of approvals to display per page
     */
    const APPROVALS_PER_PAGE = 10;

    /**
     * @Route("/approvals", name="ticket_approvals")
     * @Route("/approvals/{status}", name="ticket_approvals_by_status", requirements={"status"="[a-z]+"})
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request $request
     * @param string|null $status
     * @return Response
     */
    public function indexAction(Request $request, $status = null)
    {
        $query = $request->query->get('q');
        $status = $status ?: TicketApproval::STATUS_PENDING;

        return $this->renderThemeView('Theme:Approvals:index.html.twig', [
            'breadcrumbs' => $this->getBreadcrumbGenerator()->buildTicketApprovalList(),
            'status' => $status,
            'search_query' => $query,
            'pager' => $this->get('data.ticket_approvals')->getPager(
                $this->getUser(),
                $status,
                $query,
                $request->query->get('page', 1),
                self::APPROVALS_PER_PAGE
            )
        ]);
    }

    /**
     * @Route("/approvals/{id}", name="ticket_approvals_view", requirements={"id"="\d+"})
     * @Security("is_granted('ROLE_USER') and approval.hasApprover(user)")
     *
     * @param Request $request
     * @param TicketApproval $approval
     * @return Response
     * @throws \Exception
     */
    public function viewAction(Request $request, TicketApproval $approval)
    {
        $approvalResponse = (new ApprovalResponse())
            ->setApprover($this->getUser())
        ;

        $form = $this
            ->createForm(ApprovalResponseType::class, $approvalResponse, [
                'method' => Request::METHOD_POST,
                'action' => $this->generateUrl('ticket_approvals_view', ['id' => $approval->getId()]),
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('approve')->isClicked()) {
                $approvalResponse->setVote(ApprovalResponse::VOTE_APPROVE);
            } elseif ($form->get('reject')->isClicked()) {
                $approvalResponse->setVote(ApprovalResponse::VOTE_REJECT);
            }
            $this->getApprovalManager()->addApprovalResponse(
                $approval,
                $approvalResponse,
                $this->getApprovalManager()->createContext(ExecutorContext::METHOD_WEB, $this->getUser())
            );
        }

        return $this->renderThemeView('Theme:Approvals:view.html.twig', [
            'breadcrumbs' => $this->getBreadcrumbGenerator()->buildTicketApprovalView($approval),
            'approval' => $approval,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/approvals/{id}/approve", name="ticket_approvals_approve", requirements={"id"="\d+"})
     * @Security("is_granted('ROLE_USER') and approval.hasApprover(user)")
     * @param TicketApproval $approval
     * @return Response
     * @throws \Exception
     */
    public function approveAction(TicketApproval $approval)
    {
        $approvalResponse = (new ApprovalResponse())
            ->setApprover($this->getUser())
            ->setVote(ApprovalResponse::VOTE_APPROVE)
        ;

        try {
            $this->getApprovalManager()->addApprovalResponse(
                $approval,
                $approvalResponse,
                $this->getApprovalManager()->createContext(ExecutorContext::METHOD_WEB, $this->getUser())
            );
        } catch (\DomainException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return $this->redirectToRoute('ticket_approvals_view', ['id' => $approval->getId()]);
    }

    /**
     * @Route("/approvals/{id}/reject", name="ticket_approvals_reject", requirements={"id"="\d+"})
     * @Security("is_granted('ROLE_USER') and approval.hasApprover(user)")
     * @param TicketApproval $approval
     * @return Response
     * @throws \Exception
     */
    public function rejectAction(TicketApproval $approval)
    {
        $approvalResponse = (new ApprovalResponse())
            ->setApprover($this->getUser())
            ->setVote(ApprovalResponse::VOTE_REJECT)
        ;

        try {
            $this->getApprovalManager()->addApprovalResponse(
                $approval,
                $approvalResponse,
                $this->getApprovalManager()->createContext(ExecutorContext::METHOD_WEB, $this->getUser())
            );
        } catch (\DomainException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return $this->redirectToRoute('ticket_approvals_view', ['id' => $approval->getId()]);
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Approval\ApprovalManager
     */
    private function getApprovalManager()
    {
        return $this->get('approval.approval_manager');
    }
}
