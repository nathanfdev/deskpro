<?php

namespace Application\AgentBundle\Controller;
use Application\DeskPRO\Entity\JiraIssue;
use Application\DeskPRO\Entity\Ticket;
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
		$meta = $this->service()->getMeta();

		return $this->createJsonResponse($meta->toArray());
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

			$issueData = $this->service()->getApi()->createIssueJson($request->getContent());
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
	 * @param $ticketId
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws NotFoundHttpException
	 */
	public function issuesAction($ticketId)
	{
		$issues = $this->em->getRepository('DeskPRO:JiraIssue')->findBy(array('ticket' => $ticketId));
		$map = array();
		foreach ($issues as $issue) {
			$map[$issue['issue_id']] = $issue;
		}

		$result = null;
		if ($map) {
			$result = $this->service()->searchIssues(sprintf('id IN (%s)', implode(',', array_keys($map))));
			// cleanup deleted issues
			foreach ($result['issues'] as $data) {
				unset($map[$data['id']]);
			}
			foreach ($map as $issue) {
				$this->em->remove($issue);
			}
			$this->em->flush();
		}

		return $this->createJsonResponse($result);
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
		$rep = $this->em->getRepository('DeskPRO:JiraIssue');

		if (!$issueId) {
			$issues = $rep->findBy(array('ticket' => $ticketId));
		} else {
			if (!$issue = $rep->findOneBy(array('ticket' => $ticketId, 'issue_id' => $issueId))) {
				throw new NotFoundHttpException;
			}
			$issues = array($issue);
		}

		if (!$issues) {
			throw new NotFoundHttpException;
		}

		$js = $this->service();
		$message = $request->getContent();
		$response = array('body' => '');
		/** @var Ticket $ticket */
		$ticket = null;

		foreach ($issues as $issue) {
			$response = $js->createComment(
				$issue['issue_id'],
				'[' . $this->person->getDisplayName() . ' via DeskPRO]: ' . $message
			);

			$ticket = $ticket ?: $issue->ticket;
		}

		$manager = $this->container->getTicketManager();
		$context = $manager->createSystemExecutorContext(ExecutorContext::EVENT_UPDATE, ExecutorContext::METHOD_WEB);
		$context->setPersonContext($this->person);
		$ticket->getStateChangeRecorder()->recordData('jira.comment', $response);
		$context->getUserVars()->set('jira.comment', $response['body']);

		return $this->createJsonResponse($response);
	}

	/**
	 * search by issue key
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function searchAction(Request $request)
	{
		if (!preg_match('/[A-Za-z]+\-\d+/', $request->get('q'), $matches)) {
			return $this->createJsonResponse(null);
		}

		// todo search all matches?
		$issueId = reset($matches);
		try {
			$result = $this->service()->searchIssues('issuekey = ' . $issueId);
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

		$rep = $this->em->getRepository('DeskPRO:JiraIssue');
		if ($issue = $rep->findOneBy(array('ticket' => $ticketId, 'issue_id' => $issueId))) {
			return $this->createJsonResponse(null, 407);
		}

		// check if issue exists in jira
		$result = $this->service()->searchIssues('id = ' . $issueId);

		if (!$result['issues']) {
			return $this->createJsonResponse($result);
		}

		$issue = new JiraIssue();
		$issue['issue_id'] = $issueId;
		$fields = $result['issues'][0]['fields'];
		if (isset($fields['status'])) {
			$issue['status_id'] = $fields['status']['id'];
		}
		$issue->ticket = $ticket;

		$this->em->persist($issue);
		$this->em->flush($issue);

		$ticket->getStateChangeRecorder()->recordData('jira.linked', $result['issues'][0]);
		$manager = $this->container->getTicketManager();
		$context = $manager->createSystemExecutorContext(ExecutorContext::EVENT_UPDATE, ExecutorContext::METHOD_WEB);
		$context->setPersonContext($this->person);
		$manager->saveTicket($ticket, $context);

		return $this->createJsonResponse($result);
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

		$rep = $this->em->getRepository('DeskPRO:JiraIssue');
		$issue = $rep->findOneBy(array('ticket' => $ticketId, 'issue_id' => $issueId));
		if (!$issue) {
			throw new NotFoundHttpException;
		}

		$this->em->remove($issue);
		$this->em->flush($issue);

		return $this->createJsonResponse('success');
	}
}