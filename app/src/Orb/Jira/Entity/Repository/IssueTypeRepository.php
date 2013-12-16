<?php

namespace Jira\Entity\Repository;

use Jira\Repository;

/**
 * IssueRepository
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class IssueTypeRepository extends Repository
{
	/**
	 *
	 * @var String Entity Class
	 */
	protected $_entityClass = 'IssueType';
	
	/**
	 * Entity REST endpoint
	 * 
	 * @var String the REST endpoint
	 */
	protected $_endPoint = 'issuetype';
	
	/** {@inheritdoc} */
	protected function _create(\Jira\Entity\Entity $entity, \Jira\Service $client){}
	
	/** {@inheritdoc} */
	protected function _update(\Jira\Entity\Entity $entity, \Jira\Service $client){}
}