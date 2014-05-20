<?php

namespace Orb\Jira\Entity\Repository;

use Orb\Jira\Repository;

/**
 * IssueRepository
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class IssueRepository extends Repository
{
	/**
	 *
	 * @var String Entity Class
	 */
	protected $_entityClass = 'Issue';
	
	/**
	 * Entity REST endpoint
	 * 
	 * @var String the REST endpoint
	 */
	protected $_endPoint = 'issue';

	/**
	 * {@inheritdoc}
	 */
	protected function _create(\Orb\Jira\Entity $issue, \Orb\Jira\Service $client)
	{
		$fields = array(
			'project' => array(
				'key' => $issue->getProject()->getKey()
			),
			'description'	=> $issue->getDescription(),
			
			'summary' => $issue->getTitle(),
			
			'issuetype' => array(
				'id' => $issue->getType()->getId()
			),
			'priority' => array(
				'id' => $issue->getPriority()->getId()
			),
			'labels' => $issue->getLabels()
			//'labels' => array('test', 'test1', 'test2')
		);
		
		//var_dump($fields); die;
		
		return $client->postJson($this->getEndpoint(), array(
			'fields'	=> $fields
		));
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function _update(\Orb\Jira\Entity $issue, \Orb\Jira\Service $client)
	{
		$fields = array(
			'project' => array(
				'key' => $issue->getProject()->getKey()
			),
			'summary' => $issue->getSummary(),
			
			'issuetype' => array(
				'id' => $issue->getType()->getId()
			),
			'priority' => array(
				'id' => $issue->getPriority()->getId()
			),
			'labels' => $issue->getLabels()
		);
		
		return $client->putJson($this->getEndpoint() . '/' . $issue->getId(), array(
			'fields'	=> $fields
		));
	}
	
	public function postComment(\Orb\Jira\Entity\Issue $issue, $body)
	{
		return $this->_client->postJson($this->getEndpoint() . '/' . $issue->getId() . '/comment', array(
			'body'	=> $body
		));
	}
	
	public function getComments(\Orb\Jira\Entity\Issue $issue)
	{
		$response = $this->_client->get($this->getEndpoint() . '/' . $issue->getId() . '/comment');
		
		if ($response && isset($response['comments'])) {
			return $response['comments'];
		}
	}
}