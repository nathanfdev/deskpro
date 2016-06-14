<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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

    public function preAction($action, $arguments = null)
    {
        if (!$this->service()->isEnabled()) {
            return $this->createPermissionErrorResponse('Service is disabled');
        }

        return parent::preAction($action, $arguments);
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

        return $this->createJsonResponse($meta ? $meta->toArray() : array());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCreateMetaAction(Request $request)
    {
        return $this->createJsonResponse($this->service()->getCreateMeta($request->get('project_id')));
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
                return $this->createJsonResponse(array('errors' => $e->errors), 400);
            } else {
                return $this->createJsonResponse(array('errors' => (array) $e->getMessage()), $e->getCode());
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
        if (!$issue = $this->em->getRepository('DeskPRO:JiraIssue')->findOneBy(array('issue_id' => $issueId))) {
            throw new NotFoundHttpException();
        }

        try {
            $this->service()->updateIssueJson($issueId, $request->getContent());

            return $this->createJsonResponse(array());
        } catch (\Exception $e) {
            if ($e instanceof ApiErrorsException) {
                return $this->createJsonResponse(array('errors' => $e->errors), 400);
            } else {
                return $this->createJsonResponse(array('errors' => (array) $e->getMessage()), $e->getCode());
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
        return $this->createJsonResponse($this->service()->issues($ticketId));
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
        $response = $this->service()->addComment($request->getContent(), $ticketId, $this->person, $issueId);

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
        return $this->createJsonResponse($this->service()->searchIssues($request->get('q')));
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

        return $this->createJsonResponse($this->service()->link($ticket, $issueId, $this->person));
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

        return $this->service()->unlink($ticket, $issueId)
            ? $this->createJsonResponse('success')
            : $this->createJsonResponse(null, 404);
    }
}
