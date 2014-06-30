<?php

namespace Orb\Jira;

use Guzzle\Http\Client;

/**
 * JIRA Web Service Wrapper<br/>
 * Handles the communication to and from JIRA's REST API
 * 
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class Service
{
	/**
	 * An HTTP REST client for making<br/>
	 * REST API calls to the JIRA service
	 * 
	 * @var Client An HTTP Rest Client
	 */
	protected $_client;
	
	/**
	 * An array of errors
	 * 
	 * @var array errors
	 */
	protected $_errors	= array();
	
	/**
	 * Switches the debug mode<br/>
	 * On: Will throw an exception as and when it's caught<br/>
	 * Off: Will catch all the errors/exceptions and store<br/>
	 * it in $this->_errors array
	 * 
	 * @var bool Debug Mode
	 */
	protected $_debug	= false;
	
	protected $_em;

	/**
	 * The default constructor
	 * 
	 * @param type $params
	 */
	public function __construct($baseUrl, $params = array(), $em = null)
	{
		if (!isset($params['username']) || !isset($params['password'])) {
			throw new \Exception("You must supply your JIRA credentials to connect to JIRA");
		}
		
		$this->_client = new Client();
		
		$this->_client->addSubscriber(new \Guzzle\Plugin\CurlAuth\CurlAuthPlugin(
			$params['username'], $params['password']
		));
		
		$this->_client->setBaseUrl($baseUrl);
		
		if (isset($params['debug']) && $params['debug']) {
			$this->_debug = true;
		}
		
		$this->_em = $em;
	}
	
	/**
	 * Gets the base URL
	 * 
	 * @return String The base URL
	 */
	public function getBaseUrl()
	{
		return $this->_client->getBaseUrl();
	}
	
	/**
	 * Gets all the errors occured in the current instance
	 * 
	 * @return array An array of errors 
	 */
	public function getErrors()
	{
		return $this->_errors;
	}
	
	/**
	 * Adds a new error<br/>
	 * Based on the debug mode it either throws the exception<br/>
	 * or stores the error in the $this->_errors array
	 * 
	 * @param String $error The Error Message
	 * @return \JIRA\Service
	 * @throws \Exception if the debug mode is off
	 */
	public function addError($error)
	{
		if (!$this->_debug) {
			$this->_errors[] = $error;
		} else {
			throw new \Exception($error);
		}
		
		return $this;
	}
	
	/**
	 * Resets the current errors
	 * 
	 * @return \JIRA\Service
	 */
	public function resetErrors()
	{
		$this->_errors = array();
		
		return $this;
	}
	
	/**
	 * Sends an HTTP request
	 * 
	 * @param \Guzzle\Http\Message\Request $request
	 * @return array|string|int|bool|float
	 */
	protected function _send(\Guzzle\Http\Message\Request $request, $raw = false)
	{
		try {
			$response = $request->send();
			
			if ($response->isSuccessful()) {
				if ($response->getBody(true)) {
					if ($raw) {
						return $response->getBody(true);
					}
					return $response->json();
				}
				
				return $response;
			}
			
		} catch (\Exception $e) {
			$this->addError($e->getMessage());
		}
		
		return false;
	}

	/**
	 * Performs an HTTP GET request
	 * 
	 * @param String $uri the URI to GET
	 * @param array $params request parameters
	 * @return array|string|int|bool|float
	 */
	public function get($uri, array $headers = array(), $params = array())
	{
		$defaultParams = array(
			'timeout'         => 20,
			'connect_timeout' => 15
		);
		
		$params = array_merge($defaultParams, $params);
		
		$request = $this->_client->get($uri, $headers, $params);
		
		return $this->_send($request);
	}
	
	/**
	 * Performs an HTTP POST request
	 * 
	 * @param type $uri the URI to POST to
	 * @param array $headers additional headers to pass
	 * @param String $body request body
	 * @param String $contentType content-type to encode the body
	 * @return array|string|int|bool|float
	 */
	public function post($uri, $headers, $body, $contentType = null)
	{
		$request = $this->_client->post($uri);
		
		foreach ($headers as $key => $value) {
			$request->setHeader($key, $value);
		}
		
		$request->setBody($body, $contentType);
		
		return $this->_send($request);
	}
	
	/**
	 * Performs an HTTP PUT request
	 * 
	 * @param String $uri the URI to PUT to
	 * @param array $headers additional headers to pass
	 * @param String $body request body
	 * @param String $contentType content-type to encode the body
	 * @return array|string|int|bool|float
	 */
	public function put($uri, $headers, $body, $contentType = null)
	{
		$request = $this->_client->put($uri);
		
		foreach ($headers as $key => $value) {
			$request->setHeader($key, $value);
		}
		
		$request->setBody($body, $contentType);
		
		return $this->_send($request);
	}

	/**
	 * Performs a POST with content-type: JSON
	 * 
	 * @param String $uri the URI to POST to
	 * @param String|array $body pre encoded request body
	 * @return array|string|int|bool|float
	 */
	public function postJson($uri, $body)
	{
		$header = array(
			'Content-Type'	=> 'application/json'
		);
		
		$contentType = 'application/json';
		
		$encodedBody = $body;
		
		if (is_array($body)) {
			$encodedBody = json_encode($body);
		}
		
		return $this->post($uri, $header, $encodedBody, $contentType);
	}
	
	/**
	 * Performs a PUT with content-type: JSON
	 * 
	 * @param String $uri the URI to PUT to
	 * @param String|array $body The request body
	 * @return array|string|int|bool|float
	 */
	public function putJson($uri, $body)
	{
		$header = array(
			'Content-Type'	=> 'application/json'
		);
		
		$contentType = 'application/json';
		
		$encodedBody = $body;
		
		if (is_array($body)) {
			$encodedBody = json_encode($body);
		}
		
		return $this->put($uri, $header, $encodedBody, $contentType);
		
	}
	
	/**
	 * Performs an HTTP DELETE request
	 * 
	 * @param String $uri the URI to DELETE
	 * @return array|string|int|bool|float
	 */
	public function delete($uri)
	{
		$request = $this->_client->delete($uri);
		
		return $this->_send($request);
	}

	/**
	 * Finds an Entity
	 * 
	 * @param String $entity Entity class name
	 * @param int $id id of the entity to find
	 * @return boolean|\JIRA\Entity\Entity The object if found and "FALSE" otherwise
	 */
	public function find($entity, $id)
	{
		$repositoryClass	= $this->getRepositoryClass($entity);
		
		$repository			= $this->getRepository($repositoryClass);
		
		if (method_exists($repository, 'find')) {
			return $repository->find($id);
		}
		
		return false;
	}
	
	/**
	 * The magical __call method<br/>
	 * Maps the "findBy__EntityName" calls
	 * 
	 * @param String $name the name of the function called
	 * @param mixed $arguments additional arguments passed
	 * @return boolean|\JIRA\Entity\Entity The object if found and "FALSE" otherwise
	 */
	public function __call($name, $arguments)
	{
		$doMagic = strtolower(substr($name, 0, strlen('find'))) === 'find';
		
		if ($doMagic) {
			$entity = substr($name, strlen('find'));
			
			return $this->find('JIRA\Entity\\' . $entity, $arguments[0]);
		}
	}
	
	/**
	 * Gets the repository class of an entity
	 * 
	 * @param String|\JIRA\Entity\Entity $entity An Entity object or class name
	 * @return string|boolean The repository class name if found and "FALSE" otherwise
	 */
	public function getRepositoryClass($entity)
	{
		$entityClassName = $entity;
		
		if (is_object($entity)) {
			$entityClassName = get_class($entity);
		}
		
		$entityClassNameParts = explode('\\', $entityClassName);
		
		if (count($entityClassNameParts) >= 2) {
			$entityClassName = $entityClassNameParts[count($entityClassNameParts) - 1];
		}
		
		$repositoryClassName = __NAMESPACE__ . '\\Entity\\Repository\\' . $entityClassName . 'Repository';
		
		if (class_exists($repositoryClassName)) {
			return $repositoryClassName;
		} else {
			$this->addError('class ' . $repositoryClassName . ' could not be found');
		}
		
		return false;
	}
	
	/**
	 * Gets the Entity Repository
	 * 
	 * @param String|\JIRA\Entity\Entity $entity An Entity object or class name
	 * @return \JIRA\Entity\Repository\Repository | bool if found and "FALSE" otherwise
	 */
	public function getRepository($entity)
	{
		if (is_object($entity)) {
			$entityClass = $this->getRepositoryClass($entity);
		} else {
			$entityClass = $entity;
		}
		
		if ($entityClass) {
			return new $entityClass($this);
		}
		
		$this->addError('Invalid Class: ' . $entityClass);
	}

	/**
	 * Persists an Entity to the JIRA REST Service
	 * 
	 * @param \JIRA\Entity\Entity $entity The entity to persist
	 * @return \JIRA\Entity\Entity | boolean The persisted entity on success and "FALSE" otherwise
	 */
	public function persist(\Orb\Jira\Entity $entity)
	{
		$repository = $this->getRepository($entity);
		
		if ($repository && method_exists($repository, 'persist')) {
			return $repository->persist($entity, $this);
		}
		
		return false;
	}
	
	/**
	 * Removes and Entity from the JIRA REST Service
	 * 
	 * @param \JIRA\Entity\Entity $entity The entity to persist
	 * @return boolean "TRUE" on success and "FALSE" otherwise
	 */
	public function remove(\Orb\Jira\Entity $entity)
	{
		$repository = $this->getRepository($entity);
		
		if ($repository && method_exists($repository, 'remove')) {
			return $repository->remove($entity, $this);
		}
		
		return false;
	}
	
	public function getCreateMeta()
	{
		return $this->get('rest/api/latest/issue/createmeta', array(), array(
			'timeout'         => 15,
			'connect_timeout' => 10
		));
	}
	
	public function lookupAssignees($projectKey)
	{
		$request = $this->_client->get('rest/api/latest/user/assignable/search?project=' . $projectKey);
		
		return $this->_send($request);
	}
	
	public function lookupIssueType($projectKey)
	{
		$request = $this->_client->get('rest/api/latest/issue/createmeta?projectKeys=' . $projectKey);
		
		$response = $this->_send($request);
		
		foreach($response[$response['expand']][0]['issuetypes'] as $index => $issuetype) {
			if ($issuetype['subtask']) {
				unset($response[$response['expand']][0]['issuetypes'][$index]);
			}
		}
		
		return ($response[$response['expand']][0]['issuetypes']);
	}
	
	public function lookupPriorities($projectKey)
	{
		$request = $this->_client->get('rest/api/latest/priority?project=' . $projectKey);
		
		return $this->_send($request);
	}
	
	/**
	 * Fetches all the comments on all associated JIRA issues
	 */
	public function fetchAllComment()
	{
		//Fetch all the exported JIRA Issues
		$jiraIssueRepository = $this->_em->getRepository('Application\DeskPRO\Entity\JiraIssue');
		
		$jiraIssues = $jiraIssueRepository->findAll();
		
		foreach ($jiraIssues as $jiraIssue) {
			$issue_id = $jiraIssue->issue;
			
			$this->_fetchCommentsByIssueId($issue_id);
		}
	}
	
	/**
	 * Fetches comments on given JIRA issue
	 * 
	 * @param type $issue_id JIRA issue ID
	 */
	private function _fetchCommentsByIssueId($issue_id)
	{
		$jiraIssueRepository = $this->_em->getRepository('Application\DeskPRO\Entity\JiraIssue');
		
		$jiraIssues = $jiraIssueRepository->findBy(
			array('issue' => $issue_id
		));
		
		if (!$jiraIssues) {
			return false;
		}
		
		//Reaching this point means there are DeskPRO tickets associated to this issue_id 
		$issue		= $this->findIssue($issue_id);
		
		$jiraRepository	= $this->getRepository('\Orb\Jira\Entity\Repository\IssueRepository');
		
		$comments = $jiraRepository->getComments($issue);
		
		foreach ($jiraIssues as $jiraIssue) {
			$ticket = $jiraIssue->ticket;
			
			$savedComments = $jiraIssue->comments;
			
			//var_dump($savedComments); die;
			
			foreach ($comments as $comment) {
				foreach ($savedComments as $savedComment) {
					if ($savedComment->jiraId === (int) $comment['id']) {
						continue 2;
					}
				}
				
				$ticketNote						= new \Application\DeskPRO\Entity\TicketMessage();
				
				$ticketNote['ticket']			= $ticket;
				
				$ticketNote['ip_address']		= '10.20.30.40';
				
				$ticketNote['creation_system']	= 'app.jira';
				
				$jiraUserEmail					= $comment['author']['emailAddress'];
				
				/**
				 * Comment author mapping
				 * We're trying to find a matching user in DeskPRO
				 * If none found we set it to current user 
				 */				
				$matchedEmail = $this->_em->getRepository('Application\DeskPRO\Entity\PersonEmail')->findOneBy(array(
					'email'	=> $jiraUserEmail
				));
				
				if ($matchedEmail) {
					$commentAuthor = $matchedEmail->person;
				} else {
					$personRepository = $this->_em->getRepository('Application\DeskPRO\Entity\Person');
					
					$commentAuthor = $personRepository->find(1);
				}
				
				$ticketNote['person']			= $commentAuthor;
				
				$ticketNote->message			= $comment['body'] . '<br/><br/>' . 
						' by <a target="_blank" href="' . $this->getBaseUrl() . 'secure/ViewProfile.jspa?name=' . $comment['author']['name'] . '">' . $comment['author']['displayName'] . '</a><br/>' . 
						' in <a target="_blank" href="' . $this->getBaseUrl() . 'browse/' . $issue->getKey() . '">' . $issue->getKey() . '</a><br/>' . 
						' - JIRA';
				
				$ticketNote['is_agent_note']	= true;
				
				$ticketNote['date_created']		= new \DateTime($comment['updated']);
				
				$ticket->addMessage($ticketNote);
				
				// Set status to "Awaiting agent"
				$ticket->setStatus(\Application\DeskPRO\Entity\Ticket::STATUS_AWAITING_AGENT);
				
				$jiraIssueComment = new \Application\DeskPRO\Entity\JiraIssueComment();
				
				$jiraIssueComment->jiraId			= $comment['id'];
				$jiraIssueComment->ticketMessage	= $ticketNote;
				
				$jiraIssue->addComment($jiraIssueComment);
				
				$this->_em->persist($ticket);
				$this->_em->persist($jiraIssue);
			}
		}
		
		$this->_em->flush();
	}
}