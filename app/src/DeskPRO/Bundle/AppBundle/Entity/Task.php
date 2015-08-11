<?php

/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @category Entities
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DeskPRO\Bundle\AppBundle\Doctrine\NotifyPropertyChangeEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Application\DeskPRO\Entity\Person;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\TaskRepository")
 * @ORM\Table(name="tasks_new")
 *
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route("api_tasks_get", parameters={"id" = "expr(object.getId())"})
 * )
 */
class Task extends NotifyPropertyChangeEntity
{
    const VISIBILITY_PRIVATE = 'private';
    
    const TYPE_TASK = 'task';
    
    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id = null;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * Complete or incomplete
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $is_done = false;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $percent_complete = 0;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Assert\NotNull()
     */
    protected $date_created;

    /**
     * Either task or event
     * @var string
     * @ORM\Column(type="string")
     */
    protected $task_type = self::TYPE_TASK;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date_due;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date_event_start;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date_event_end;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="creator_person_id", referencedColumnName="id")
     * @Assert\NotNull()
     * @Assert\Valid()
     */
    protected $creator;

    /**
     * Project, public or private
     * @var string
     * @ORM\Column(type="string")
     */
    protected $visibility = self::VISIBILITY_PRIVATE;

    /**
     * @var TaskProject
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TaskProject")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $project;

    /**
     * @var TaskList
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TaskList")
     * @ORM\JoinColumn(name="list_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $list;

    /**
     * Between 1 and 10
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $urgency = 5;

    /**
     * @var TaskSubtask[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="TaskSubtask", mappedBy="task")
     */
    protected $subtasks;

    /**
     * @var LabelTask[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="LabelTask", mappedBy="task", cascade={"persist"})
     */
    protected $labels;

    /**
     * @var TaskComment[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="TaskComment", mappedBy="task")
     */
    protected $comments;

    /**
     * @var TaskAttachment[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="TaskAttachment", mappedBy="task")
     */
    protected $attachments;

    /**
     * @var TaskLinkedItem[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="TaskLinkedItem", mappedBy="task")
     */
    protected $linked_items;

    /**
     * @var TaskAssignment[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="TaskAssignment", mappedBy="task")
     */
    protected $assigned;

    /**
     * @param Person $creator
     */
    public function __construct(Person $creator)
    {
        $this->subtasks = new ArrayCollection();
        $this->labels = new ArrayCollection();
        $this->setCreator($creator);
        $this->setDateCreated(new \DateTime());
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function isDone()
    {
        return $this->is_done;
    }

    /**
     * @return int
     */
    public function getPercentComplete()
    {
        return $this->percent_complete;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @return string
     */
    public function getTaskType()
    {
        return $this->task_type;
    }

    /**
     * @return \DateTime
     */
    public function getDateDue()
    {
        return $this->date_due;
    }

    /**
     * @return \DateTime
     */
    public function getDateEventStart()
    {
        return $this->date_event_start;
    }

    /**
     * @return \DateTime
     */
    public function getDateEventEnd()
    {
        return $this->date_event_end;
    }

    /**
     * @return Person
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @return TaskProject
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return TaskList
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @return int
     */
    public function getUrgency()
    {
        return $this->urgency;
    }

    /**
     * @return TaskSubtask[]|ArrayCollection
     */
    public function getSubtasks()
    {
        return $this->subtasks;
    }

    /**
     * @return LabelTask[]|ArrayCollection
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @return TaskComment[]|ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @return TaskAttachment[]|ArrayCollection
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @return TaskLinkedItem[]|ArrayCollection
     */
    public function getLinkedItems()
    {
        return $this->linked_items;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);
    }

    /**
     * @param string $is_done
     */
    public function setIsDone($is_done)
    {
        $this->setModelField('is_done', $is_done);
    }

    /**
     * @param int $percent_complete
     */
    public function setPercentComplete($percent_complete)
    {
        $this->setModelField('percent_complete', $percent_complete);
    }

    /**
     * @param \DateTime $date_created
     */
    protected function setDateCreated(\DateTime $date_created)
    {
        $this->setModelField('date_created', $date_created);
    }

    /**
     * @param string $task_type
     */
    public function setTaskType($task_type)
    {
        $this->setModelField('task_type', $task_type);
    }

    /**
     * @param \DateTime $date_due
     */
    public function setDateDue($date_due)
    {
        $this->setModelField('date_due', $date_due);
    }

    /**
     * @param \DateTime $date_event_start
     */
    public function setDateEventStart($date_event_start)
    {
        $this->setModelField('date_event_start', $date_event_start);
    }

    /**
     * @param \DateTime $date_event_end
     */
    public function setDateEventEnd($date_event_end)
    {
        $this->setModelField('date_event_end', $date_event_end);
    }

    /**
     * @param Person $creator
     */
    public function setCreator($creator)
    {
        $this->setModelField('creator', $creator);
    }

    /**
     * @param string $visibility
     */
    public function setVisibility($visibility)
    {
        $this->setModelField('visibility', $visibility);
    }

    /**
     * @param TaskProject $project
     */
    public function setProject($project)
    {
        $this->setModelField('project', $project);
    }

    /**
     * @param TaskList $list
     */
    public function setList($list)
    {
        $this->setModelField('list', $list);
    }

    /**
     * @param int $urgency
     */
    public function setUrgency($urgency)
    {
        $this->setModelField('urgency', $urgency);
    }

    /**
     * @param TaskSubtask $subtask
     */
    public function addSubtask(TaskSubtask $subtask)
    {
        $this->subtasks->add($subtask);
        $this->setModelField('task', $subtask);
    }

    /**
     * @param LabelTask $label
     */
    public function addLabel(LabelTask $label)
    {
        $this->labels->add($label);
        $this->setModelField('label', $label);
    }

    public function removeLabel(LabelTask $label)
    {
        $this->labels->remove($label);
    }

    /**
     * @param TaskComment $comment
     */
    public function addComment(TaskComment $comment)
    {
        $this->comments->add($comment);
        $this->setModelField('comment', $comment);
    }

    /**
     * @param TaskAttachment $attachment
     */
    public function addAttachment(TaskAttachment $attachment)
    {
        $this->attachments->add($attachment);
        $this->setModelField('attachment', $attachment);
    }

    /**
     * @param TaskLinkedItem $linked_item
     */
    public function addLinkedItem(TaskLinkedItem $linked_item)
    {
        $this->linked_items->add($linked_item);
        $this->setModelField('linked_item', $linked_item);
    }

    /**
     * @return TaskAssignment[]|ArrayCollection
     */
    public function getAssigned()
    {
        return $this->assigned;
    }
}
