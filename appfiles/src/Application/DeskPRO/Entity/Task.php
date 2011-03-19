<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Ricardo Rauch <ricardo@gravityonmars.com>
 */

namespace Application\DeskPRO\Entity;

/**
 * Task entity definition
 *
 * @orm:Entity
 * @orm:Table(name="tasks")
 */
class Task extends \Application\DeskPRO\Domain\DomainObject
{
	
	/**
	 * Private visibility constant.
	 * @var int
	 */
	const PRIVATE_VISIBILITY = 0;
	
	/**
	 * Team visibility constant.
	 * @var int
	 */
	const TEAM_VISIBILITY = 1;
	
	/**
	 * Public visibility constant.
	 * @var int
	 */
	const PUBLIC_VISIBILITY = 2;
	
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id
	 * @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * Whether this task is completed
	 *
	 * @var bool
	 * @orm:Column(name="is_completed", type="boolean")
	 */
	protected $is_completed = false;

	/**
	 * The task's title
	 *
	 * @var string
	 * @orm:Column(name="title", type="text")
	 */
	protected $title = '';

	/**
	 * The task's visibility. On of: self::PRIVATE_VISIBILITY,
	 * self::TEAM_VISIBILITY or self::PUBLIC_VISIBILITY. 
	 *
	 * @var int
	 * @orm:Column(name="visibility", type="integer")
	 */
	protected $visibility = 0;
	
	/**
	 * The task's optional due date.
	 * 
	 * @var DateTime
	 * @orm:Column(name="date_due", type="date", nullable=true)
	 */
	protected $date_due = null;

	/**
	 * The date the task was inserted into the system
	 *
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;
	
	/**
	 * The date the task was completed
	 *
	 * @var \DateTime
	 * @orm:Column(name="date_completed",type="datetime", nullable=true)
	 */
	protected $date_completed;
	
	/**
	 * @var Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", inversedBy="created_tasks")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", nullable=false)
	 */
	protected $person;
	
	/**
	 * @var Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", inversedBy="assigned_tasks")
	 * @orm:JoinColumn(name="assigned_agent_id", referencedColumnName="id", nullable=true)
	 */
	protected $assigned_agent;
	
	/**
	 * @var Application\DeskPRO\Entity\AgentTeam
	 * @orm:ManyToOne(targetEntity="AgentTeam", inversedBy="assigned_tasks")
	 * @orm:JoinColumn(name="assigned_agent_team_id", referencedColumnName="id", nullable=true)
	 */
	protected $assigned_agent_team;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="LabelTask", mappedBy="task")
	 */
	protected $labels;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TaskComment", mappedBy="task")
	 */
	protected $comments;
	
	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TaskAssociation", mappedBy="task")
	 */
	protected $task_associations;
	


	/**
	 * Creates a new Task 
	 */
	public function __construct()
	{
		$this->labels            = new \Doctrine\Common\Collections\ArrayCollection();
		$this->comments          = new \Doctrine\Common\Collections\ArrayCollection();
		$this->task_associations = new \Doctrine\Common\Collections\ArrayCollection();

		$this->date_created = new \DateTime();
	}



	/**
	 * Sets the task visibility.
	 * 
	 * @param int $visibility One of: self::PRIVATE_VISIBILITY,
	 * 												self::TEAM_VISIBILITY or self::PUBLIC_VISIBILITY
	 * @throws \InvalidArgumentException Thrown when the visibility is not valid.
	 */
	public function setVisibility($visibility)
	{
		if (! $this->isValidVisibility($visibility)) {
			throw new \InvalidArgumentException('Invalid visibility');
		}
		
		$old_visibility = $this->visibility;
		$this->visibility = $visibility;
		$this->_onPropertyChanged('visibility', $old_visibility, $visibility);
	}
	
	
	
	/**
	 * Returns whether the visibility is valid or not.
	 * 
	 * @param int $visibility The visibility to check
	 * @return bool
	 */
	protected function isValidVisibility($visibility)
	{
		return in_array(
			$visibility,
			array(self::PRIVATE_VISIBILITY, self::TEAM_VISIBILITY, self::PUBLIC_VISIBILITY)
		);
	}
	
	
	
	/**
	 * Returns whether the task has been delegated or not. Tasks assigned to its
	 * creator are not considered delegated.
	 * 
	 * @return bool
	 */
	public function isDelegated()
	{
		if ($this->assigned_agent !== null) {
			return ($this->person['id'] !== $this->assigned_agent['id']);
		}
		
		return ($this->assigned_agent !== null);
	}
	
	
	
	/**
	 * Returns the task's person id.
	 * 
	 * @return int
	 */
	public function getPersonId()
	{
		return $this->person['id'];
	}

	
	
	/**
	 * Sets the task's person id.
	 * 
	 * @param int id The agent's id.
	 * @throws \InvalidArgumentException Thrown when there's no preson with that
	 *                                   id or the person is not an agent.
	 */
	public function setPersonId($id)
	{
		if ($this->person['id'] == $id) {
			return;
		}
	
		$person = App::getEntityRepository('DeskPRO:Person')->find($id);
		
		if (! $person) {
			throw new \InvalidArgumentException('No agent for id ' . $id);
		}
		
		if (! $person->isAgent) {
			throw new \InvalidArgumentException(
				'The person with id ' . $id . ' is not an agent'
			);
		}
	
		$this->person->tasks->remove($this);
		$this->person = $person;	
	}
	
	
	
	/**
	 * Returns the task's assigned agent's id.
	 * 
	 * @return int
	 */
	public function getAsignedAgentId()
	{
		if (! $this->assigned_agent) {
			return 0;
		}
		
		return $this->assigned_agent['id'];
	}

	
	
	/**
	 * Sets the task's assigned agent's id.
	 * 
	 * @param int id The agent's id.
	 * @throws \InvalidArgumentException Thrown when there's no preson with that
	 *                                   id or the person is not an agent.
	 */
	public function setAsignedAgentId($id)
	{
		$agent = App::getEntityRepository('DeskPRO:Person')->find($id);
		
		if (! $agent) {
			throw new \InvalidArgumentException('No agent for id ' . $id);
		}
		
		if (! $agent->isAgent) {
			throw new \InvalidArgumentException(
				'The person with id ' . $id . ' is not an agent'
			);
		}
		
		$this->assigned_agent = $agent;
	}

	
	
	/**
	 * Returns the task's assigned agent team's id.
	 * 
	 * @return int
	 */
	public function getAsignedAgentTeamId()
	{
		if (! $this->assigned_agent_team) {
			return 0;
		}
		
		return $this->assigned_agent_team['id'];
	}

	
	
	/**
	 * Sets the task's assigned agent team's id.
	 * 
	 * @param int id The agent team's id.
	 * @throws \InvalidArgumentException Thrown when there's no team with that id
	 */
	public function setAsignedAgentTeamId($id)
	{
		$agent_team = App::getEntityRepository('DeskPRO:AgentTeam')->find($id);
		
		if (! $agent_team) {
			throw new \InvalidArgumentException('No agent team for id ' . $id);
		}
		
		$this->assigned_agent_team = $agent_team;
	}
	
	
	
	/**
	 * Adds a label
	 * @param \Application\DeskPRO\Entity\LabelTicket $label
	 */
	public function addLabel(LabelTask $label)
	{
		$label['ticket'] = $this;
		$this->labels->add($label);
	}

	
	
	/**
	 * Adds a comment to the task.
	 *
	 * @param \Application\DeskPRO\Entity\Person $author The comment's author
	 * @param string $comment_content The comment's content
	 */
	public function addComment(Person $author, $comment_content)
	{
		$comment = new TaskComment($author, $comment_content);
		$comment->task = $this;
		
		$this->comments->add($comment);
	}
	
	
	
}

