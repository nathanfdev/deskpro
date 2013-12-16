<?php

namespace Jira\Entity\Repository;

use Jira\Repository;

/**
 * Issue Priority Repository
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class PriorityRepository extends Repository
{
	/**
	 *
	 * @var String Entity Class
	 */
	protected $_entityClass = 'Priority';
	
	/**
	 * Entity REST endpoint
	 * 
	 * @var String the REST endpoint
	 */
	protected $_endPoint = 'priority';
	
	/** {@inheritdoc} */
	protected function _create(\Jira\Entity\Entity $entity, \Jira\Service $client){}
	
	/** {@inheritdoc} */
	protected function _update(\Jira\Entity\Entity $entity, \Jira\Service $client){}
}