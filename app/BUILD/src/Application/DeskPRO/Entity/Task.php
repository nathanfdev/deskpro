<?php

/**
 * DeskPRO.
 *
 * @category  Entities
 *
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Dates;

/**
 * Task entity definition.
 */
class Task extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * Private visibility constant.
     *
     * @var int
     */
    const PRIVATE_VISIBILITY = 0;

    /**
     * Public visibility constant.
     *
     * @var int
     */
    const PUBLIC_VISIBILITY = 1;

    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Whether this task is completed.
     *
     * @var bool
     */
    protected $is_completed = false;

    /**
     * The task's title.
     *
     * @var string
     */
    protected $title = '';

    /**
     * The task's visibility. On of: self::PRIVATE_VISIBILITY(0) or self::PUBLIC_VISIBILITY(1).
     *
     * @var int
     */
    protected $visibility = self::PUBLIC_VISIBILITY;

    /**
     * The task's optional due date.
     *
     * @var \DateTime
     */
    protected $date_due = null;

    /**
     * The date the task was inserted into the system.
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * The date the task was completed.
     *
     * @var \DateTime
     */
    protected $date_completed;

    /**
     * The person created this task.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * The person assigned to complete this task.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $assigned_agent;

    /**
     * The department assigned to complete this task.
     *
     * @var \Application\DeskPRO\Entity\Department
     */
    protected $assigned_department;

    /**
     * The agent team assigned to complete this task.
     *
     * @var \Application\DeskPRO\Entity\AgentTeam
     */
    protected $assigned_agent_team;

    /**
     * Labels associated with this task.
     */
    protected $labels;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $comments;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $task_associations;

    /**
     * Label manager for adding/removing labels.
     *
     * @var \Application\DeskPRO\Labels\LabelManager
     */
    protected $_label_manager = null;

    /**
     * Creates a new Task.
     */
    public function __construct()
    {
        $this->labels            = new ArrayCollection();
        $this->comments          = new ArrayCollection();
        $this->task_associations = new ArrayCollection();

        $this->date_created = new \DateTime();
        $this->visibility   = self::PUBLIC_VISIBILITY;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function isOverdue()
    {
        if (!$this->date_due) {
            return false;
        }

        return $this->date_due->getTimestamp() < time();
    }

    public function isDueToday(Person $person_context = null)
    {
        if (!$this->date_due) {
            return true;
        }

        if ($person_context) {
            $tz = $person_context->getDateTimezone();
        } else {
            $tz = Dates::tzUtc();
        }

        $tomorrow = new \DateTime('now', $tz);
        $tomorrow->setTime(0, 0, 0);

        $cmp_tomorrow = clone $this->date_due;
        $cmp_tomorrow->setTimezone($tz);

        return $cmp_tomorrow->format('Y-m-d') == $tomorrow->format('Y-m-d');
    }

    public function isDueTomorrow(Person $person_context = null)
    {
        if (!$this->date_due) {
            return true;
        }

        if ($person_context) {
            $tz = $person_context->getDateTimezone();
        } else {
            $tz = Dates::tzUtc();
        }

        $tomorrow = new \DateTime('now', $tz);
        $tomorrow->setTime(0, 0, 0);
        $tomorrow->modify('+1 day');

        $cmp_tomorrow = clone $this->date_due;
        $cmp_tomorrow->modify('+1 day');
        $cmp_tomorrow->setTimezone($tz);

        return $cmp_tomorrow->format('Y-m-d') == $tomorrow->format('Y-m-d');
    }

    /**
     * Sets the task visibility.
     *
     * @param int $visibility One of: self::PRIVATE_VISIBILITY or self::PUBLIC_VISIBILITY
     *
     * @throws \InvalidArgumentException Thrown when the visibility is not valid
     */
    public function setVisibility($visibility)
    {
        if (!$this->isValidVisibility($visibility)) {
            throw new \InvalidArgumentException('Invalid visibility');
        }

        $this->setModelField('visibility', (int) $visibility);
    }

    /**
     * Returns whether the visibility is valid or not.
     *
     * @param int $visibility The visibility to check
     *
     * @return bool
     */
    protected function isValidVisibility($visibility)
    {
        return in_array(
            $visibility,
            [self::PRIVATE_VISIBILITY, self::PUBLIC_VISIBILITY]
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
            return $this->person['id'] !== $this->assigned_agent['id'];
        }

        return $this->assigned_agent !== null;
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

    public function setCompleted($yes_no)
    {
        if ($yes_no) {
            $this->setModelField('is_completed', true);
            $this->setModelField('date_completed', new \DateTime());
        } else {
            $this->setModelField('is_completed', false);
            $this->setModelField('date_completed', null);
        }
    }

    /**
     * Sets the task's person id.
     *
     * @param int $id The agent's id
     *
     * @throws \InvalidArgumentException Thrown when there's no preson with that
     *                                   id or the person is not an agent
     */
    public function setPersonId($id)
    {
        if ($this->person['id'] == $id) {
            return;
        }

        $person = App::getEntityRepository('DeskPRO:Person')->find($id);

        if (!$person) {
            throw new \InvalidArgumentException('No agent for id '.$id);
        }

        if (!$person->isAgent) {
            throw new \InvalidArgumentException(
                'The person with id '.$id.' is not an agent'
            );
        }

        $this->person->tasks->remove($this);
        $this['person'] = $person;
    }

    /**
     * @param string $t
     */
    public function setTitle($t)
    {
        $t = $t ? trim($t) : '';
        $this->setModelField('title', $t);
    }

    /**
     * Returns the task's assigned agent's id.
     *
     * @return int
     */
    public function getAsignedAgentId()
    {
        if (!$this->assigned_agent) {
            return 0;
        }

        return $this->assigned_agent['id'];
    }

    /**
     * Sets the task's assigned agent's id.
     *
     * @param int id The agent's id
     *
     * @throws \InvalidArgumentException Thrown when there's no preson with that
     *                                   id or the person is not an agent
     */
    public function setAsignedAgentId($id)
    {
        $agent = App::getEntityRepository('DeskPRO:Person')->find($id);

        if (!$agent) {
            throw new \InvalidArgumentException('No agent for id '.$id);
        }

        if (!$agent->is_agent) {
            throw new \InvalidArgumentException(
                'The person with id '.$id.' is not an agent'
            );
        }

        $this['assigned_agent'] = $agent;
        if ($agent) {
            $this->setModelField('assigned_agent_team', null);
        }
    }

    /**
     * Returns the task's assigned agent team's id.
     *
     * @return int
     */
    public function getAsignedAgentTeamId()
    {
        if (!$this->assigned_agent_team) {
            return 0;
        }

        return $this->assigned_agent_team['id'];
    }

    /**
     * Sets the task's assigned agent team's id.
     *
     * @param int $id The agent team's id
     *
     * @throws \InvalidArgumentException Thrown when there's no team with that id
     */
    public function setAsignedAgentTeamId($id)
    {
        $agent_team = App::getEntityRepository('DeskPRO:AgentTeam')->find($id);

        if (!$agent_team) {
            throw new \InvalidArgumentException('No agent team for id '.$id);
        }

        $this['assigned_agent_team'] = $agent_team;
        if ($agent_team) {
            $this->setModelField('assigned_agent', null);
        }
    }

    /**
     * @return Department
     */
    public function getAssignedDepartment()
    {
        return $this->assigned_department;
    }

    /**
     * @param Department $department
     *
     * @return $this
     */
    public function setAssignedDepartment(Department $department = null)
    {
        $this->setModelField('assigned_department', $department);

        return $this;
    }

    /**
     * Adds a label.
     *
     * @param LabelTask $label
     *
     * @return $this
     */
    public function addLabel(LabelTask $label)
    {
        $label->setTask($this);
        $this->labels->add($label);

        return $this;
    }

    /**
     * Adds a comment to the task.
     *
     * @param Person $author          The comment's author
     * @param string $comment_content The comment's content
     */
    public function addComment(Person $author, $comment_content)
    {
        $comment       = new TaskComment($author, $comment_content);
        $comment->task = $this;

        $this->comments->add($comment);
    }

    public function getLabelManager()
    {
        if ($this->_label_manager === null) {
            $this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelTask');
        }

        return $this->_label_manager;
    }

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);
        if ($deep) {
            $data['labels'] = [];
            foreach ($this->labels as $label) {
                $data['labels'][] = $label['label'];
            }
        }

        return $data;
    }

    /**
     * @return bool
     */
    public function hasDueTime()
    {
        if (!$this->date_due) {
            return false;
        }

        return $this->date_due->format('i') !== '59';
    }

    /**
     * Set isCompleted.
     *
     * @param bool $isCompleted
     *
     * @return Task
     */
    public function setIsCompleted($isCompleted)
    {
        $this->setModelField('is_completed', $isCompleted);

        return $this;
    }

    /**
     * Get isCompleted.
     *
     * @return bool
     */
    public function getIsCompleted()
    {
        return $this->is_completed;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get visibility.
     *
     * @return int
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Set dateDue.
     *
     * @param \DateTime $dateDue
     *
     * @return Task
     */
    public function setDateDue($dateDue)
    {
        $this->setModelField('date_due', $dateDue);

        return $this;
    }

    /**
     * Get dateDue.
     *
     * @return \DateTime
     */
    public function getDateDue()
    {
        return $this->date_due;
    }

    /**
     * Set dateCreated.
     *
     * @param \DateTime $dateCreated
     *
     * @return Task
     */
    public function setDateCreated($dateCreated)
    {
        $this->setModelField('date_created', $dateCreated);

        return $this;
    }

    /**
     * Get dateCreated.
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set dateCompleted.
     *
     * @param \DateTime $dateCompleted
     *
     * @return Task
     */
    public function setDateCompleted($dateCompleted)
    {
        $this->setModelField('date_completed', $dateCompleted);

        return $this;
    }

    /**
     * Get dateCompleted.
     *
     * @return \DateTime
     */
    public function getDateCompleted()
    {
        return $this->date_completed;
    }

    /**
     * Set person.
     *
     * @param \Application\DeskPRO\Entity\Person $person
     *
     * @return Task
     */
    public function setPerson(\Application\DeskPRO\Entity\Person $person = null)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * Get person.
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Set assignedAgent.
     *
     * @param \Application\DeskPRO\Entity\Person $assignedAgent
     *
     * @return Task
     */
    public function setAssignedAgent(\Application\DeskPRO\Entity\Person $assignedAgent = null)
    {
        $this->setModelField('assigned_agent', $assignedAgent);

        return $this;
    }

    /**
     * Get assignedAgent.
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getAssignedAgent()
    {
        return $this->assigned_agent;
    }

    /**
     * Set assignedAgentTeam.
     *
     * @param \Application\DeskPRO\Entity\AgentTeam $assignedAgentTeam
     *
     * @return Task
     */
    public function setAssignedAgentTeam(\Application\DeskPRO\Entity\AgentTeam $assignedAgentTeam = null)
    {
        $this->setModelField('assigned_agent_team', $assignedAgentTeam);

        return $this;
    }

    /**
     * Get assignedAgentTeam.
     *
     * @return \Application\DeskPRO\Entity\AgentTeam
     */
    public function getAssignedAgentTeam()
    {
        return $this->assigned_agent_team;
    }

    /**
     * Remove label.
     *
     * @param \Application\DeskPRO\Entity\LabelTask $label
     */
    public function removeLabel(\Application\DeskPRO\Entity\LabelTask $label)
    {
        $this->labels->removeElement($label);
    }

    /**
     * Get labels.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Remove comment.
     *
     * @param \Application\DeskPRO\Entity\TaskComment $comment
     */
    public function removeComment(\Application\DeskPRO\Entity\TaskComment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add taskAssociation.
     *
     * @param \Application\DeskPRO\Entity\TaskAssociation $taskAssociation
     *
     * @return Task
     */
    public function addTaskAssociation(\Application\DeskPRO\Entity\TaskAssociation $taskAssociation)
    {
        $this->task_associations[] = $taskAssociation;

        return $this;
    }

    /**
     * Remove taskAssociation.
     *
     * @param \Application\DeskPRO\Entity\TaskAssociation $taskAssociation
     */
    public function removeTaskAssociation(\Application\DeskPRO\Entity\TaskAssociation $taskAssociation)
    {
        $this->task_associations->removeElement($taskAssociation);
    }

    /**
     * Get taskAssociations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTaskAssociations()
    {
        return $this->task_associations;
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("tickets")
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Ticket>>")
     *
     * @return Ticket[]
     */
    public function getTickets()
    {
        return $this->task_associations
            ->filter(function ($assoc) {
                return $assoc instanceof TaskAssociatedTicket;
            })
            ->map(function ($assoc) {
                return $assoc->getTicket();
            });
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Task';
        $metadata->setPrimaryTable(['name' => 'tasks']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_completed',
                'type'       => 'boolean',
                'columnName' => 'is_completed',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'title',
                'type'       => 'text',
                'columnName' => 'title',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'visibility',
                'type'       => 'integer',
                'columnName' => 'visibility',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_due',
                'type'       => 'datetime',
                'nullable'   => true,
                'columnName' => 'date_due',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_created',
                'type'       => 'datetime',
                'columnName' => 'date_created',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_completed',
                'type'       => 'datetime',
                'nullable'   => true,
                'columnName' => 'date_completed',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => Person::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'assigned_agent',
                'targetEntity' => Person::class,
                'mappedBy'     => null,
                'inversedBy'   => 'assigned_tasks',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'assigned_agent_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'assigned_department',
                'targetEntity' => Department::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'assigned_department_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'assigned_agent_team',
                'targetEntity' => AgentTeam::class,
                'mappedBy'     => null,
                'inversedBy'   => 'assigned_tasks',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'assigned_agent_team_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'labels',
                'targetEntity' => LabelTask::class,
                'cascade'      => [
                    0 => 'remove',
                    1 => 'persist',
                    3 => 'merge',
                ],
                'mappedBy'      => 'task',
                'orderBy'       => ['label' => 'ASC'],
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'comments',
                'targetEntity' => TaskComment::class,
                'mappedBy'     => 'task',
                'dpApi'        => true,
                'dpApiDeep'    => true,
                'dpApiPrimary' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'task_associations',
                'targetEntity' => TaskAssociation::class,
                'cascade'      => [
                    0 => 'remove',
                    1 => 'persist',
                    3 => 'merge',
                ],
                'mappedBy'      => 'task',
                'orphanRemoval' => true,
                'dpApi'         => true,
                'dpApiDeep'     => true,
            ]
        );
    }
}
