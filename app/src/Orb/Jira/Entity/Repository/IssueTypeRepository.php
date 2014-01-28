<?php

namespace Orb\Jira\Entity\Repository;

use Orb\Jira\Repository;

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
	protected function _create(\Orb\JIRA\Entity $entity, \Orb\Jira\Service $client){}
	
	/** {@inheritdoc} */
	protected function _update(\Orb\Jira\Entity $entity, \Orb\Jira\Service $client){}
}