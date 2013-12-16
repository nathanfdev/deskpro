<?php

namespace Jira\Entity\Repository;

use Jira\Repository;

/**
 * Jira Project Repository
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class ProjectRepository extends Repository
{
	/**
	 *
	 * @var String Entity Class
	 */
	protected $_entityClass = 'Project';
	
	/**
	 * Entity REST endpoint
	 * 
	 * @var String the REST endpoint
	 */
	protected $_endPoint = 'project';
	
	/** {@inheritdoc} */
	protected function _create(\Jira\Entity\Entity $entity, \Jira\Service $client){}
	
	/** {@inheritdoc} */
	protected function _update(\Jira\Entity\Entity $entity, \Jira\Service $client){}
}