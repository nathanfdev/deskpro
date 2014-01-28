<?php

namespace Orb\Jira\Entity;

use Orb\Jira\Entity;

/**
 * The IssueType Class
 * Emulates a Jira issue type
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class IssueType extends Entity
{
	/** is it a subtask? */
	protected $_subtask = false;
	
	/**
	 * {@inheritdoc}
	 * 
	 */
	public function __construct(array $params = array())
	{	
		if (!isset($params['id']) || !isset($params['name'])) {
			return false;
		}
		
		$this->_id	= $params['id'];
		
		$this->setName($params['name']);
		
		if (isset($params['description'])) {
			$this->setDescription($params['description']);
		}
		
		if (isset($params['subtask']) && $params['subtask']) {
			$this->setSubTask();
		}
		
		if (isset($params['iconUrl'])) {
			$this->addAvatar($params['iconUrl']);
		}
		
		return $this;
	}
	
	/**
	 * Checks if it's a subtask
	 * 
	 * @return bool "TRUE" if it's a subtask and "FALSE" otherwise
	 */
	public function isSubTask()
	{
		return $this->_subtask;
	}
	
	/**
	 * Sets this to be a subtask
	 * 
	 * @return \Orb\Jira\Entity\IssueType
	 */
	public function setSubTask()
	{
		$this->_subtask = true;
		
		return $this;
	}
}