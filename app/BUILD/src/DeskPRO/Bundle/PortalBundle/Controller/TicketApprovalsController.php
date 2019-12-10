<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use DeskPRO\Bundle\AppBundle\Approval\ExecutorContext;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\ApprovalResponseType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TicketApprovalsController
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
     *
     * @return Response
     */
    public function indexAction(Request $request, $status = null)
    {
        $query  = $request->query->get('q');
        $params = [
            'person'       => $this->getUser(),
            'breadcrumbs'  => $this->getBreadcrumbGenerator()->buildTicketApprovalList(),
            'status'       => $status,
            'search_query' => $query,
        ];

        if ($this->isHelpCenterTheme()) {
            if ($request->query->has('status')) {
                $statuses = $request->get('status');
            } else {
                $statuses = [
                    TicketApproval::STATUS_PENDING,
                    TicketApproval::STATUS_APPROVED,
                    TicketApproval::STATUS_REJECTED,
                    TicketApproval::STATUS_CANCELLED,
                ];
            }

            if ($request->query->has('time_completion')) {
                $timeCompletion = $request->query->get('time_completion');
            } else {
                $timeCompletion = [];
            }

            $params['approval_list_tables'] = $this->makeApprovalListTables($query, $statuses, $timeCompletion, $request);
            $params['statuses']             = $statuses;
            $params['time_completion']      = $timeCompletion;
        } else {
            $status = $status ?: TicketApproval::STATUS_PENDING;
            $page   = $request->query->get('page', 1);

            $params['status'] = $status;
            $params['pager']  = $this->get('data.ticket_approvals')->getPager(
                $this->getUser(),
                $status,
                $query,
                $page,
                self::APPROVALS_PER_PAGE
            );
        }

        return $this->renderThemeView('Theme:Approvals:index.html.twig', $params);
    }

    /**
     * @Route("/approvals/{id}", name="ticket_approvals_view", requirements={"id"="\d+"})
     * @Security("is_granted('ROLE_USER') and approval.hasApprover(user)")
     *
     * @param Request $request
     * @param TicketApproval $approval
     *
     * @throws \Exception
     *
     * @return Response
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

            if (!$approval->hasResponded($this->getUser())) {
                $this->getApprovalManager()->addApprovalResponse(
                    $approval,
                    $approvalResponse,
                    $this->getApprovalManager()->createContext(ExecutorContext::METHOD_WEB, $this->getUser())
                );

                if ($approvalResponse->isApproved()) {
                    $this->addFlash('success', $this->phrase('portal.flashes.ticket-approvals.approved-by', [
                        'person'   => $this->getUser()->getCommunityName(),
                        'template' => $approval->getTemplate()->getName(),
                    ]));
                } else {
                    $this->addFlash('success', $this->phrase('portal.flashes.ticket-approvals.rejected-by', [
                        'person'   => $this->getUser()->getCommunityName(),
                        'template' => $approval->getTemplate()->getName(),
                    ]));
                }
            }
        }

        return $this->renderThemeView('Theme:Approvals:view.html.twig', [
            'person'      => $this->getUser(),
            'breadcrumbs' => $this->getBreadcrumbGenerator()->buildTicketApprovalView($approval),
            'approval'    => $approval,
            'form'        => $form->createView(),
        ]);
    }

    /**
     * @Route("/approvals/{id}/approve", name="ticket_approvals_approve", requirements={"id"="\d+"})
     * @Security("is_granted('ROLE_USER') and approval.hasApprover(user)")
     *
     * @param TicketApproval $approval
     *
     * @throws \Exception
     *
     * @return Response
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
            $this->addFlash('success', $this->phrase('portal.flashes.ticket-approvals.approved-by', [
                'person'   => $this->getUser()->getCommunityName(),
                'template' => $approval->getTemplate()->getName(),
            ]));
        } catch (\DomainException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return $this->redirectToRoute('ticket_approvals_view', ['id' => $approval->getId()]);
    }

    /**
     * @Route("/approvals/{id}/reject", name="ticket_approvals_reject", requirements={"id"="\d+"})
     * @Security("is_granted('ROLE_USER') and approval.hasApprover(user)")
     *
     * @param TicketApproval $approval
     *
     * @throws \Exception
     *
     * @return Response
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
            $this->addFlash('success', $this->phrase('portal.flashes.ticket-approvals.rejected-by', [
                'person'   => $this->getUser()->getCommunityName(),
                'template' => $approval->getTemplate()->getName(),
            ]));
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

    /**
     * @param string  $query
     * @param array   $statuses
     * @param array   $timeIntervals
     * @param Request $request
     *
     * @return array
     */
    private function makeApprovalListTables($query, array $statuses, array $timeIntervals, Request $request)
    {
        $tables = [];
        foreach ($statuses as $status) {
            $sort      = $request->query->get($status.'_sort');
            $direction = $request->query->get($status.'_direction', 'desc');

            $tables[] = [
                'status'    => $status,
                'sort'      => $sort,
                'direction' => $direction,
                'pager'     => $this->get('data.ticket_approvals')->getPager(
                    $this->getUser(),
                    $status,
                    $query,
                    $request->query->get($status.'_page', 1),
                    self::APPROVALS_PER_PAGE,
                    $sort,
                    $direction,
                    $timeIntervals
                ),
            ];
        }

        return $tables;
    }
}
