<?php

namespace Orb\Jira\Entity;

use Orb\Jira\Entity;

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
     * @var String The issue assignee
     */
    protected $_assignee;

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
     * Issue labels
     *
     * @var array An array of labels
     */
    protected $_labels;

    /**
     * Issue Status
     *
     * @var String
     */
    protected $_status;

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

        if (isset($params['fields']['status'])) {
            $this->setStatus($params['fields']['status']['name']);
        }

        if (isset($params['fields']['assignee'])) {
            $this->setAssignee($params['fields']['assignee']['displayName']);
        }

        if (isset($params['fields']['labels'])) {
            $this->setLabels($params['fields']['labels']);
        }

        return $this;
    }

    public function getTitle()
    {
        return $this->_name;
    }

    public function setTitle($title)
    {
        $this->_name = $title;

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
     * @param  String          $summary The new Description to set
     * @return \Orb\Jira\Issue
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
     * @param  \Orb\Jira\Entity\IssueType $type The issue type to set
     * @return \Orb\Jira\Entity\Issue
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
     * @param  \Orb\Jira\Entity\Project $project The project to set
     * @return \Orb\Jira\Entity\Issue
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
     * @param  \Orb\Jira\Entity\Priority $priority The issue priority to set
     * @return \Orb\Jira\Entity\Issue
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
     * @param  String                 $dueDate The new due date
     * @return \Orb\Jira\Entity\Issue
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

    /**
     * Adds an issue label
     *
     * @param  String                 $label label to add
     * @return \Orb\Jira\Entity\Issue
     */
    public function addLabel($label)
    {
        $this->_labels[] = $label;

        return $this;
    }

    /**
     * Sets the issue labels
     *
     * @param  array                  $labels
     * @return \Orb\Jira\Entity\Issue
     */
    public function setLabels(array $labels)
    {
        $this->_labels = $labels;

        return $this;
    }

    /**
     * Get Labels
     *
     * @return array An array of labels
     */
    public function getLabels()
    {
        return $this->_labels;
    }

    /**
     * Get the issue status
     *
     * @return String issue status
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Sets the status
     *
     * @param  String                 $status The new staus
     * @return \Orb\Jira\Entity\Issue
     */
    public function setStatus($status)
    {
        $this->_status = $status;

        return $this;
    }

    /**
     * Gets the assignee
     *
     * @return String the assignee
     */
    public function getAssignee()
    {
        return $this->_assignee;
    }

    /**
     * Sets the assignee
     *
     * @param  String                 $assignee
     * @return \Orb\Jira\Entity\Issue
     */
    public function setAssignee($assignee)
    {
        $this->_assignee = $assignee;

        return $this;
    }
}
