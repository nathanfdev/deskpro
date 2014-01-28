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
		$service = $this->_getService();
		
		$em = $this->__get('em');
		
		$repo = $em->getRepository('Application\DeskPRO\Entity\JiraIssue');
				
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
		
		if ('POST' === $this->request->getMethod()) {
			return $this->_processPost($ticket);
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
	
	protected function _processPost(\Application\DeskPRO\Entity\Ticket $ticket)
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
		
		if ($response && isset($response['id'])) {
			$newIssue->setId($response['id']);
			
			$newIssue->setKey($response['key']);
			
			$jiraIssue = new \Application\DeskPRO\Entity\JiraIssue();
			
			$jiraIssue->ticket = $ticket;

			$jiraIssue->issue = $newIssue->getId();

			$em = $this->__get('em');
			
			$em->persist($jiraIssue);
			
			$em->flush();

			return $this->createJsonResponse($response);	
				
		}
			
		return $this->createJsonResponse(
			array(
				'message'	=> 'There was an error in creatign the issue, please try again'
		), 500);
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
	
	public function getAssociatedIssuesAction($ticket_id = null)
	{
		$service = $this->_getService();
		
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
		
		$repository = $this->__get('em')->getRepository('Application\DeskPRO\Entity\JiraIssue');
		
		$jiraIssues = $repository->findBy(
			array('ticket' => $ticket
		));
		
		$transformedIssues = array();
		
		foreach ($jiraIssues as $issue) {
			$transformedIssues[] = $service->findIssue($issue->issue);
		}
		
		return $this->render('AgentBundle:Jira:issues-table.html.twig', array(
			'jirabaseurl'	=> \Application\DeskPRO\App::getSetting('core.apps_jira.baseUrl'),
			'issues'		=> $transformedIssues
		));
	}
	
	public function getCommentsAction($issue_id = null)
	{
		$service	= $this->_getService();
		
		$issue		= $service->findIssue($issue_id);
		
		$jiraRepository	= $service->getRepository('\Orb\Jira\Entity\Repository\IssueRepository');
		
		$comments = $jiraRepository->getComments($issue);
		
		$jiraIssueRepository = $this->__get('em')->getRepository('Application\DeskPRO\Entity\JiraIssue');
		
		$success = 0;
		
		$jiraIssues = $jiraIssueRepository->findBy(
			array('issue' => $issue_id
		));
		
		foreach ($jiraIssues as $jiraIssue) {
			$ticket = $jiraIssue->ticket;
			
			foreach ($comments as $comment) {
				$ticketNote						= new \Application\DeskPRO\Entity\TicketMessage();
				
				$ticketNote['ticket']			= $ticket;
				
				$ticketNote['message']			= $this->person;
				
				$ticketNote['ip_address']		= dp_get_user_ip_address();
				
				$ticketNote['creation_system']	= 'app.jira';
				
				$ticketNote['person']			= $this->person;
				
				$ticketNote->message			= $comment['body'] . '<br/><br/>' . 
						' by <a target="_blank" href="' . $service->getBaseUrl() . 'secure/ViewProfile.jspa?name=' . $comment['author']['name'] . '">' . $comment['author']['displayName'] . '</a><br/>' . 
						' in <a target="_blank" href="' . $service->getBaseUrl() . 'browse/' . $issue->getKey() . '">' . $issue->getKey() . '</a><br/>' . 
						' - JIRA';
				
				$ticketNote['is_agent_note']	= true;
				
				$ticketNote['date_created']		= new \DateTime($comment['updated']);
				
				$ticket->addMessage($ticketNote);
				
				$this->__get('em')->persist($ticket);
			}
		}
		
		$this->__get('em')->flush();
		
		var_dump($comments); die;
	}
	
	public function postCommentAction($issue_id = null)
	{
		$service = $this->_getService();
		
		$repository = $this->__get('em')->getRepository('Application\DeskPRO\Entity\JiraIssue');
		
		$success = 0;
		
		$jiraIssues = $repository->findOneBy(
			array('issue' => $issue_id
		));
		
		if ($jiraIssues && $jiraIssues instanceof \Application\DeskPRO\Entity\JiraIssue) {
			
			if ('POST' === $this->request->getMethod()) {
				$postParams = $this->request->request->all();
				
				if (!isset($postParams['comment'])) {
					throw new \Exception('Comment can not be empty');
				}
				
				$comment = $postParams['comment'];
				
				$repository	= $service->getRepository('\Orb\Jira\Entity\Repository\IssueRepository');
				
				$issue		= $service->findIssue($issue_id);
				
				$response = $repository->postComment($issue, $comment);
				
				if ($response) {
					$success = 1;
				}
				return $this->createJsonResponse(array(
					'success'	=> $success
				));
			}

			return $this->render('AgentBundle:Jira:post-comment.html.twig', array(
				'issue_id'	=> $issue_id
			));
		}
		
		throw $this->createNotFoundException('Invalid Issue ID');
	}
}