<?php

namespace Application\AgentBundle\Controller;

/**
 * The JiraController class
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class JiraController extends AbstractController
{
	public function exportAction($ticket_id)
	{
		if ('POST' === $this->request->getMethod()) {
			return $this->_processPost();
		}
		
		$service = $this->_getService();
		
		$meta = $service->getCreateMeta(); 
		
		$projects = array();
		
		foreach ($meta[$meta['expand']] as $projectParams) {
			$projects[] = \Orb\Jira\Entity\Project::fromArray($projectParams);
		}
		
		//$ticket_id = (int) $ticket_id;
		
		try	{
			$ticket = $this->getTicketOr404($ticket_id);
		} catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
			// try to find a delete log
			$delete_log = $this->em->getRepository('DeskPRO:TicketDeleted')->findOneBy(array('ticket_id' => $ticket_id));
			if ($delete_log) {
				return $this->render('AgentBundle:Ticket:deleted.html.twig', array('delete_log' => $delete_log));
			} else {
				throw $e;
			}
		}
		
		$description = array();
		
		foreach ($ticket->messages as $message) {
			$created = get_object_vars($message->date_created);
			
			if ($message->person->is_user) {
				$personType = 'User';
			} elseif ($message->person->is_agent) {
				$personType = 'Agent';
			} else {
				$personType = 'System';
			}
			
			$person = $message->person . '(' . $personType . ')';
			
			$divider = PHP_EOL . PHP_EOL . str_repeat('-', 60) . PHP_EOL . PHP_EOL;
			
			$description[] = $person . ' at ' . 
					$created['date'] . PHP_EOL . 
					str_repeat('-', 60) . PHP_EOL . PHP_EOL . 
					strip_tags($message->message) .
					$divider;
		}
		
		$divider = PHP_EOL . PHP_EOL . str_repeat('=', 60) . PHP_EOL . PHP_EOL;
		
		return $this->render('AgentBundle:Jira:export-overlay.html.twig', array(
			'ticket'		=> $ticket,
			'description'	=> implode('', $description),
			'projects'		=> $projects,
		));
	}
	
	protected function _getService()
	{
		$baseUrl	= \Application\DeskPRO\App::getSetting('core.apps_jira.baseUrl');
		
		$username	= \Application\DeskPRO\App::getSetting('core.apps_jira.username');
		
		$password	= \Application\DeskPRO\App::getSetting('core.apps_jira.password');
		
		$service = new \Orb\Jira\Service($baseUrl, array(
			'username'	=> $username,
			'password'	=> $password,
			'debug'		=> true
		));
		
		return $service;
	}
	
	public function lookupAction()
	{
		//$param		= $this->request->get('param');
		$projectKey	= $this->request->get('projectkey');
		
		$assignee	= $this->_lookupAssignee($projectKey);
		$issueTypes	= $this->_lookupIssueType($projectKey);
		$priorities	= $this->_lookupPriorities($projectKey);
		
		$payload = array(
			'assignee'		=> $assignee,
			'issuetypes'	=> $issueTypes,
			'priorities'	=> $priorities,
		);
		
		//echo json_encode($payload); die;
		
		return $this->render('AgentBundle:Jira:lookup.html.twig', array(
			'payload'	=> $payload
		));
	}
	
	protected function _lookupAssignee($projectKey)
	{
		$service = $this->_getService();
		
		$response = $service->lookupAssignees($projectKey);
		
		foreach ($response as &$assignee) {
			$assignee['avatarUrls']['xsmall']	= $assignee['avatarUrls']['16x16'];
			$assignee['avatarUrls']['small']	= $assignee['avatarUrls']['24x24'];
			$assignee['avatarUrls']['medium']	= $assignee['avatarUrls']['32x32'];
		}
		
		return $response;
	}
	
	
	protected function _lookupIssueType($projectKey)
	{
		$service = $this->_getService();
		
		return $service->lookupIssueType($projectKey);
	}
	
	protected function _lookupPriorities($projectKey)
	{
		$service = $this->_getService();
		
		return $service->lookupPriorities($projectKey);
	}
	
	protected function _processPost()
	{
		$service = $this->_getService();
		
		$postParams = $this->request->request->all();
			
		$newIssue = new \Orb\Jira\Entity\Issue();

		$newIssue->setDescription($postParams['description'])
				->setType($service->findIssueType($postParams['issuetype']))
				->setTitle($postParams['title'])
				->setProject($service->findProject($postParams['project']))
				->setPriority($service->findPriority($postParams['priority']))
				->setLabels(explode(',', $postParams['labels']));
		
		//var_dump($newIssue); die;

		$response = $service->persist($newIssue);
		
		return $this->createJsonResponse($response);
	}


	/**
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	protected function getTicketOr404($ticket_id, $check_perm = null)
	{
		$q = $this->em->createQuery("
			SELECT t, person, person_primary_email, agent,
				agent_team, language, department, product, category, workflow, priority,
				organization, locked_by_agent
			FROM DeskPRO:Ticket t
			LEFT JOIN t.person person
			LEFT JOIN person.primary_email person_primary_email
			LEFT JOIN t.agent agent
			LEFT JOIN t.agent_team agent_team
			LEFT JOIN t.language language
			LEFT JOIN t.department department
			LEFT JOIN t.product product
			LEFT JOIN t.category category
			LEFT JOIN t.workflow workflow
			LEFT JOIN t.priority priority
			LEFT JOIN t.organization organization
			LEFT JOIN t.locked_by_agent locked_by_agent
			WHERE t.id = ?0
		");
		$q->setParameters(array($ticket_id));

		$ticket = $q->getOneOrNullResult();

		// If no ticket, check the delete log in case it was merged since
		if (!$ticket) {
			$merged_ticket_id = $this->em->getRepository('DeskPRO:Ticket')->findTicketId($ticket_id);
			if ($merged_ticket_id) {
				return $this->getTicketOr404($merged_ticket_id, $check_perm);
			}
		}

		if (!$ticket) {
			throw $this->createNotFoundException("There is no ticket with ID $ticket_id");
		}

		if (!$this->person->PermissionsManager->TicketChecker->canView($ticket)) {
			throw new \Application\DeskPRO\HttpKernel\Exception\NoPermissionException("You are not allowed to view this ticket");
		}

		if ($check_perm && !$this->checkPerm($ticket, $check_perm)) {
			throw new \Application\DeskPRO\HttpKernel\Exception\NoPermissionException("There is no ticket with ID $ticket_id");
		}

		return $ticket;
	}
}