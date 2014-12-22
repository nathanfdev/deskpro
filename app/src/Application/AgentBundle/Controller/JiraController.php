<?php

namespace Application\AgentBundle\Controller;
use Application\DeskPRO\Entity\JiraIssue;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\JIRA\ApiCoreException;
use Application\DeskPRO\JIRA\ApiErrorsException;
use Application\DeskPRO\Service\JIRA;
use Application\DeskPRO\Tickets\ExecutorContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The JiraController class
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
			throw new NotFoundHttpException;
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

		return $this->createJsonResponse($meta ? $meta->toArray() : null);
	}

	/**
	 * @param Request $request
	 * @param $ticketId
	 * @return \Symfony\Component\HttpFoundation\Response
     * @throws NotFoundHttpException
	 */
	public function createIssueAction(Request $request, $ticketId)
	{
		if (!$ticket = $this->em->find('DeskPRO:Ticket', $ticketId)) {
			throw new NotFoundHttpException;
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateIssueAction(Request $request, $issueId)
    {
        if (!$issue = $this->em->getRepository('DeskPRO:JiraIssue')->findOneBy(array('issue_id' => $issueId))) {
            throw new NotFoundHttpException;
        }

        try {

            $this->service()->updateIssueJson($issueId, $request->getContent());
            return $this->createJsonResponse(true);

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
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function issuesAction($ticketId)
	{
		$issues = $this->em->getRepository('DeskPRO:JiraIssue')->findBy(array('ticket' => $ticketId));
		$map = array();
		foreach ($issues as $issue) {
			$map[$issue['issue_id']] = $issue;
		}

		$result = null;
		if (!$map) {
            return $this->createJsonResponse($result);
        }

        try {

            $result = $this->service()->searchIssues(sprintf('id IN (%s)', implode(',', array_keys($map))));
            return $this->createJsonResponse($result);

        } catch (ApiCoreException $e) {

            foreach ($e->errors as $error) {
                if (!preg_match('/A value with ID \'(\d+)\' does not exist for the field \'id\'\./', $error, $matches)) {
                    continue;
                }
                if (isset($map[$matches[1]])) {
                    $this->em->remove($map[$matches[1]]);
                }
            }
            $this->em->flush();

            return $this->issuesAction($ticketId);
        }
	}

	/**
	 * @param Request $request
	 * @param $ticketId
	 * @param $issueId
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws NotFoundHttpException
	 */
	public function addCommentAction(Request $request, $ticketId, $issueId)
	{
        $response = $this->service()->addComment($request->getContent(), $ticketId, $this->person, $issueId);
		return $this->createJsonResponse($response);
	}

	/**
	 * search by issue key
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function searchAction(Request $request)
	{
        $q = trim($request->get('q'));
        $query = sprintf('summary ~ "%s*"', $q);

        // issue key
		if (preg_match('/^[A-Za-z]+\-\d+/', $q, $matches)) {
            $query = sprintf('issuekey = %s or ', mb_strtoupper(reset($matches))) . $query;
		}

        try {
            $result = $this->service()->searchIssues($query);
        } catch (\Exception $e) {
            $result = null;
        }

        return $this->createJsonResponse($result);

	}

	/**
	 * link issue to ticket
	 * @param $ticketId
	 * @param $issueId
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws NotFoundHttpException
	 */
	public function linkAction($ticketId, $issueId)
	{
		/** @var $ticket Ticket */
		if (!$ticket = $this->em->find('DeskPRO:Ticket', $ticketId)) {
			throw new NotFoundHttpException;
		}

		return $this->createJsonResponse($this->service()->link($ticket, $issueId, $this->person));
	}

	/**
	 * unlink issue
	 * @param $ticketId
	 * @param $issueId
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws NotFoundHttpException
	 */
	public function unlinkAction($ticketId, $issueId)
	{
		if (!$ticket = $this->em->find('DeskPRO:Ticket', $ticketId)) {
			throw new NotFoundHttpException;
		}

        return $this->service()->unlink($ticket, $issueId)
            ? $this->createJsonResponse('success')
            : $this->createJsonResponse(null, 404);
	}
}