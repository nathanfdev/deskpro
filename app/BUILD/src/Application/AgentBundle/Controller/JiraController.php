<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\JIRA\ApiErrorsException;
use Application\DeskPRO\Service\JIRA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The JiraController class.
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class JiraController extends AbstractController
{
    /** @var JIRA */
    protected $service;

    public function preActionHandler(Request $request, $action, $arguments = null)
    {
        if (!$this->service()->isEnabled()) {
            return $this->createPermissionErrorResponse('Service is disabled');
        }

        return parent::preActionHandler($request, $action, $arguments);
    }

    /**
     * @return JIRA
     */
    protected function service()
    {
        if (!$this->service) {
            $this->service = $this->get(JIRA::NAME);
        }

        return $this->service;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getMetaAction()
    {
        try {
            $meta = $this->service()->getMeta();
        } catch (\Exception $e) {
            $meta = null;
        }

        return $this->createJsonResponse($meta ? $meta->toArray() : []);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCreateMetaAction(Request $request)
    {
        try {
            return $this->createJsonResponse($this->service()->getCreateMeta($request->get('project_id')));
        } catch (\Exception $e) {
            if ($e instanceof ApiErrorsException) {
                return $this->createJsonResponse(['errors' => $e->errors], 400);
            } else {
                return $this->createJsonResponse(['errors' => (array) $e->getMessage()], 400);
            }
        }
    }

    /**
     * @param Request $request
     * @param $ticketId
     *
     * @throws NotFoundHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createIssueAction(Request $request, $ticketId)
    {
        if (!$ticket = $this->em->find('DeskPRO:Ticket', $ticketId)) {
            throw new NotFoundHttpException();
        }

        try {
            $issueData = $this->service()->createIssueJson($request->getContent());

            return $this->linkAction($ticketId, $issueData['id']);
        } catch (\Exception $e) {
            if ($e instanceof ApiErrorsException) {
                return $this->createJsonResponse(['errors' => $e->errors], 400);
            } else {
                return $this->createJsonResponse(['errors' => (array) $e->getMessage()], 400);
            }
        }
    }

    /**
     * @param Request $request
     * @param $issueId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateIssueAction(Request $request, $issueId)
    {
        if (!$issue = $this->em->getRepository('DeskPRO:JiraIssue')->findOneBy(['issue_id' => $issueId])) {
            throw new NotFoundHttpException();
        }

        try {
            $this->service()->updateIssueJson($issueId, $request->getContent());

            return $this->createJsonResponse([]);
        } catch (\Exception $e) {
            if ($e instanceof ApiErrorsException) {
                return $this->createJsonResponse(['errors' => $e->errors], 400);
            } else {
                return $this->createJsonResponse(['errors' => (array) $e->getMessage()], 400);
            }
        }
    }

    /**
     * @param $ticketId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function issuesAction($ticketId)
    {
        try {
            return $this->createJsonResponse($this->service()->issues($ticketId));
        } catch (\Exception $e) {
            if ($e instanceof ApiErrorsException) {
                return $this->createJsonResponse(['errors' => $e->errors], 400);
            } else {
                return $this->createJsonResponse(['errors' => (array) $e->getMessage()], 400);
            }
        }
    }

    /**
     * @param Request $request
     * @param $ticketId
     * @param $issueId
     *
     * @throws NotFoundHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addCommentAction(Request $request, $ticketId, $issueId)
    {
        try {
            $response = $this->service()->addComment($request->getContent(), $ticketId, $this->person, $issueId);
        } catch (\Exception $e) {
            if ($e instanceof ApiErrorsException) {
                return $this->createJsonResponse(['errors' => $e->errors], 400);
            } else {
                return $this->createJsonResponse(['errors' => (array) $e->getMessage()], $e->getCode());
            }
        }

        return $this->createJsonResponse($response);
    }

    /**
     * search by issue key.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        try {
            return $this->createJsonResponse($this->service()->searchIssues($request->get('q')));
        } catch (\Exception $e) {
            if ($e instanceof ApiErrorsException) {
                return $this->createJsonResponse(['errors' => $e->errors], 400);
            } else {
                return $this->createJsonResponse(['errors' => (array) $e->getMessage()], 400);
            }
        }
    }

    /**
     * link issue to ticket.
     *
     * @param $ticketId
     * @param $issueId
     *
     * @throws NotFoundHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function linkAction($ticketId, $issueId)
    {
        /** @var $ticket Ticket */
        if (!$ticket = $this->em->find('DeskPRO:Ticket', $ticketId)) {
            throw new NotFoundHttpException();
        }

        try {
            return $this->createJsonResponse($this->service()->link($ticket, $issueId, $this->person));
        } catch (\Exception $e) {
            if ($e instanceof ApiErrorsException) {
                return $this->createJsonResponse(['errors' => $e->errors], 400);
            } else {
                return $this->createJsonResponse(['errors' => (array) $e->getMessage()], 400);
            }
        }
    }

    /**
     * unlink issue.
     *
     * @param $ticketId
     * @param $issueId
     *
     * @throws NotFoundHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function unlinkAction($ticketId, $issueId)
    {
        if (!$ticket = $this->em->find('DeskPRO:Ticket', $ticketId)) {
            throw new NotFoundHttpException();
        }

        try {
            return $this->service()->unlink($ticket, $issueId)
                ? $this->createJsonResponse('success')
                : $this->createJsonResponse(null, 404);
        } catch (\Exception $e) {
            if ($e instanceof ApiErrorsException) {
                return $this->createJsonResponse(['errors' => $e->errors], 400);
            } else {
                return $this->createJsonResponse(['errors' => (array) $e->getMessage()], 400);
            }
        }
    }
}
