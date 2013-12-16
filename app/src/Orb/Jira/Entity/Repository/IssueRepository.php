<?php

namespace Jira\Entity\Repository;

use Jira\Repository;

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
	protected function _create(\Jira\Entity\Entity $issue, \Jira\Service $client)
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
				'id' => $issue->getPrriority()->getId()
			),
			'labels' => $client->getLabels()
		);
		
		return $client->postJson($this->getEndpoint(), array(
			'fields'	=> $fields
		));
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function _update(\Jira\Entity\Entity $issue, \Jira\Service $client)
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
				'id' => $issue->getPrriority()->getId()
			),
			'labels' => $client->getLabels()
		);
		
		return $client->putJson($this->getEndpoint() . '/' . $issue->getId(), array(
			'fields'	=> $fields
		));
	}
}