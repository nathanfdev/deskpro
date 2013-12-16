<?php

namespace Jira\Entity;

use Jira\Entity;

/**
 * The Issue Class
 * Emulates a Jira Issue
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class Issue extends Entity
{
	/**
	 * Issue type
	 * 
	 * @var IssueType The issue type
	 */
	protected $_issueType;
	
	/**
	 * Project under which the issue is logged
	 * 
	 * @var Project Jira Project
	 */
	protected $_project;


	/**
	 * Issue priority
	 * 
	 * @var Priority The issue priority
	 */
	protected $_priority;
	
	/**
	 * The issue due date
	 * 
	 * @var int The issue due date
	 */
	protected $_dueDate;
	
	/**
	 * The issue assignee
	 * 
	 * @var Person The issue assignee
	 */
	protected $_assgignee;
	
	/**
	 * Issue Creation Date/time
	 * 
	 * @var String Issue creation date/time in UTC
	 */
	protected $_created;
	
	/**
	 * Issue Updation Date/time
	 * 
	 * @var String Issue updation date/time in UTC
	 */
	protected $_updated;
	
	/**
	 * {@inheritdoc}
	 */
	public function __construct(array $params = array())
	{		
		if (!isset($params['id']) || !isset($params['key'])) {
			return false;
		}
		
		$this->_id	= $params['id'];
		
		$this->setKey($params['key']);
		
		if (isset($params['fields']['summary'])) {
			$this->setDescription($params['fields']['summary']);
		}
		
		if (isset($params['fields']['duedate'])) {
			$this->setDueDate($params['fields']['duedate']);
		}
		
		if (isset($params['fields']['created'])) {
			$this->_created = $params['fields']['created'];
		}
		
		if (isset($params['fields']['updated'])) {
			$this->_updated = $params['fields']['updated'];
		}
		
		if (isset($params['fields']['issuetype'])) {
			$this->setType(IssueType::fromArray($params['fields']['issuetype']));
		}
		
		if (isset($params['fields']['project'])) {
			$this->setProject(Project::fromArray($params['fields']['project']));
		}
		
		if (isset($params['fields']['priority'])) {
			$this->setPriority(Priority::fromArray($params['fields']['priority']));
		}
		
		return $this;
	}
	
	/**
	 * Gets the Description.
	 * 
	 * @return String The issue description.
	 */
	public function getSummary()
	{
		return $this->getDescription();
	}
	
	/**
	 * Sets the description
	 * 
	 * @param String $summary The new Description to set
	 * @return \Jira\Issue
	 */
	public function setSummary($summary)
	{
		return $this->setDescription($summary);
	}
	
	/**
	 * Get the issue type
	 * 
	 * @return IssueType The issue type
	 */
	public function getType()
	{
		return $this->_issueType;
	}
	
	/**
	 * Sets the issue type
	 * 
	 * @param \Jira\Entity\IssueType $type The issue type to set
	 * @return \Jira\Entity\Issue
	 */
	public function setType(IssueType $type)
	{
		$this->_issueType = $type;
		
		return $this;
	}
	
	/**
	 * Gets the issue project
	 * 
	 * @return Project
	 */
	public function getProject()
	{
		return $this->_project;
	}
	
	/**
	 * Sets the issue project
	 * 
	 * @param \Jira\Entity\Project $project The project to set
	 * @return \Jira\Entity\Issue
	 */
	public function setProject(Project $project)
	{
		$this->_project = $project;
		
		return $this;
	}
	
	/**
	 * Gets the issue priority
	 * 
	 * @return Priority Issue Priority
	 */
	public function getPriority()
	{
		return $this->_priority;
	}
	
	/**
	 * Sets the issue priority
	 * 
	 * @param \Jira\Entity\Priority $priority The issue priority to set
	 * @return \Jira\Entity\Issue
	 */
	public function setPriority(Priority $priority)
	{
		$this->_priority = $priority;
		
		return $this;
	}
	
	/**
	 * Gets the due date
	 * 
	 * @return String
	 */
	public function getDueDate()
	{
		return $this->_dueDate;
	}
	
	/**
	 * Sets the due date
	 * 
	 * @param String $dueDate The new due date
	 * @return \Jira\Entity\Issue
	 */
	public function setDueDate($dueDate)
	{
		$this->_dueDate = $dueDate;
		
		return $this;
	}
	
	/**
	 * Gets the created time
	 * 
	 * @return String The created time
	 */
	public function getCreated()
	{
		return $this->_created;
	}
	
	/**
	 * Gets the updated time
	 * 
	 * @return String The updated time
	 */
	public function getUpdated()
	{
		return $this->_updated;
	}
}