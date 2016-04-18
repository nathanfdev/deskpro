<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedArticle;
use DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedChat;
use DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedTicket;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\TaskRepository")
 * @ORM\Table(name="tasks_new")
 * @ORM\HasLifecycleCallbacks
 */
class Task implements EntityInterface
{
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_PUBLIC  = 'public';
    const VISIBILITY_PROJECT = 'project';

    const TYPE_TASK  = 'task';
    const TYPE_EVENT = 'event';

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
     *
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * Complete or incomplete.
     *
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
     *
     * @Assert\NotNull()
     */
    protected $date_created;

    /**
     * Either task or event.
     *
     * @var string
     * @ORM\Column(type="string")
     *
     * @Assert\NotNull()
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
     * @ORM\JoinColumn(name="creator_person_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $creator;

    /**
     * Project, public or private.
     *
     * @var string
     * @ORM\Column(type="string")
     *
     * @Assert\NotNull()
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
     * Between 1 and 10.
     *
     * @var int
     * @ORM\Column(type="integer")
     *
     * @Assert\NotNull()
     */
    protected $urgency = 5;

    /**
     * @var TaskSubtask[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="TaskSubtask", mappedBy="task")
     */
    protected $subtasks;

    /**
     * @var LabelTask[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="LabelTask", mappedBy="task", cascade={"all"}, orphanRemoval=true)
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
     * @var TaskLinkedArticle[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedArticle", mappedBy="task",
     *     cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $linked_articles;

    /**
     * @var TaskLinkedChat[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedChat", mappedBy="task",
     *     cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $linked_chats;

    /**
     * @var TaskLinkedTicket[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedTicket", mappedBy="task",
     *     cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $linked_tickets;

    /**
     * @var TaskAssignment[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="TaskAssignment", mappedBy="task", cascade={"persist"}, orphanRemoval=true)
     */
    protected $assigned;

    /**
     * The date the task was completed.
     *
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date_done;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $display_order = 1;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $for_del = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->subtasks        = new ArrayCollection();
        $this->labels          = new ArrayCollection();
        $this->assigned        = new ArrayCollection();
        $this->linked_articles = new ArrayCollection();
        $this->linked_chats    = new ArrayCollection();
        $this->linked_tickets  = new ArrayCollection();

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
     * @return bool
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
     * @return TaskLinkedArticle[]|ArrayCollection
     */
    public function getLinkedArticles()
    {
        return $this->linked_articles;
    }

    /**
     * @return TaskLinkedChat[]|ArrayCollection
     */
    public function getLinkedChats()
    {
        return $this->linked_chats;
    }

    /**
     * @return TaskLinkedTicket[]|ArrayCollection
     */
    public function getLinkedTickets()
    {
        return $this->linked_tickets;
    }

    /**
     * @return \DateTime
     */
    public function getDateDone()
    {
        return $this->date_done;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param bool $isDone
     *
     * @return $this
     */
    public function setIsDone($isDone = true)
    {
        $date_done = $isDone ? new \DateTime() : null;

        $this->setDateDone($date_done);
        $this->is_done = $isDone;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getDepartments()
    {
        $departments = [];
        if (!empty($this->assigned)) {
            foreach ($this->assigned as $assigned) {
                if (!empty($assigned->getDepartment())) {
                    $departments[] = $assigned->getDepartment();
                }
            }
        }

        return new ArrayCollection($departments);
    }

    /**
     * @return ArrayCollection
     */
    public function getTeams()
    {
        $departments = [];
        if (!empty($this->assigned)) {
            foreach ($this->assigned as $assigned) {
                if (!empty($assigned->getTeam())) {
                    $departments[] = $assigned->getTeam();
                }
            }
        }

        return new ArrayCollection($departments);
    }

    /**
     * @return ArrayCollection
     */
    public function getAgents()
    {
        $departments = [];
        if (!empty($this->assigned)) {
            foreach ($this->assigned as $assigned) {
                if (!empty($assigned->getPerson())) {
                    $departments[] = $assigned->getPerson();
                }
            }
        }

        return new ArrayCollection($departments);
    }

    /**
     * @param Department $department
     */
    public function addDepartment(Department $department)
    {
        $assigned = new TaskAssignment();
        $assigned->setDepartment($department);
        $assigned->setTask($this);
        $this->addAssigned($assigned);
    }

    /**
     * @param Department $department
     */
    public function removeDepartment(Department $department)
    {
        if (!empty($this->assigned)) {
            foreach ($this->assigned as $assigned) {
                if ($assigned->getDepartment() === $department) {
                    $this->assigned->removeElement($assigned);
                }
            }
        }
    }

    /**
     * @param AgentTeam $agentTeam
     */
    public function addTeam(AgentTeam $agentTeam)
    {
        $assigned = new TaskAssignment();
        $assigned->setTeam($agentTeam);
        $assigned->setTask($this);
        $this->addAssigned($assigned);
    }

    /**
     * @param AgentTeam $agentTeam
     */
    public function removeTeam(AgentTeam $agentTeam)
    {
        if (!empty($this->assigned)) {
            foreach ($this->assigned as $assigned) {
                if ($assigned->getTeam() === $agentTeam) {
                    $this->assigned->removeElement($assigned);
                }
            }
        }
    }

    /**
     * @param Person $person
     */
    public function addAgent(Person $person)
    {
        $assignment = new TaskAssignment();
        $assignment->setPerson($person);
        $assignment->setTask($this);
        $this->addAssigned($assignment);
    }

    /**
     * @param Person $person
     */
    public function removeAgent(Person $person)
    {
        if (!empty($this->assigned)) {
            foreach ($this->assigned as $assignment) {
                if ($assignment->getPerson() === $person) {
                    $this->assigned->removeElement($assignment);
                }
            }
        }
    }

    /**
     * @param TaskAssignment $assignment
     */
    public function addAssigned(TaskAssignment $assignment)
    {
        $this->assigned->add($assignment);
    }

    /**
     * @param TaskAssignment $assignment
     */
    public function removeAssigned(TaskAssignment $assignment)
    {
        $this->assigned->removeElement($assignment);
    }

    /**
     * @return bool
     */
    public function isForDel()
    {
        return $this->for_del;
    }

    /**
     * @param bool $bool
     *
     * @return $this
     */
    public function setForDel($bool)
    {
        $this->for_del = $bool;

        return $this;
    }

    /**
     * Get isDone.
     *
     * @return bool
     */
    public function getIsDone()
    {
        return $this->is_done;
    }

    /**
     * Get forDel.
     *
     * @return bool
     */
    public function getForDel()
    {
        return $this->for_del;
    }

    /**
     * Remove subtask.
     *
     * @param TaskSubtask $subtask
     */
    public function removeSubtask(TaskSubtask $subtask)
    {
        $this->subtasks->removeElement($subtask);
    }

    /**
     * Remove comment.
     *
     * @param TaskComment $comment
     */
    public function removeComment(TaskComment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Remove attachment.
     *
     * @param TaskAttachment $attachment
     */
    public function removeAttachment(TaskAttachment $attachment)
    {
        $this->attachments->removeElement($attachment);
    }

    /**
     * Remove linkedArticle.
     *
     * @param TaskLinkedArticle $linkedArticle
     */
    public function removeLinkedArticle(TaskLinkedArticle $linkedArticle)
    {
        $this->linked_articles->removeElement($linkedArticle);
    }

    /**
     * Remove linkedChat.
     *
     * @param TaskLinkedChat $linkedChat
     */
    public function removeLinkedChat(TaskLinkedChat $linkedChat)
    {
        $this->linked_chats->removeElement($linkedChat);
    }

    /**
     * Remove linkedTicket.
     *
     * @param TaskLinkedTicket $linkedTicket
     */
    public function removeLinkedTicket(TaskLinkedTicket $linkedTicket)
    {
        $this->linked_tickets->removeElement($linkedTicket);
    }

    /**
     * Set percentComplete.
     *
     * @param int $percentComplete
     *
     * @return $this
     */
    public function setPercentComplete($percentComplete)
    {
        $this->percent_complete = $percentComplete;

        return $this;
    }

    /**
     * Set dateCreated.
     *
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated($dateCreated)
    {
        $this->date_created = $dateCreated;

        return $this;
    }

    /**
     * Set taskType.
     *
     * @param string $taskType
     *
     * @return $this
     */
    public function setTaskType($taskType)
    {
        $this->task_type = $taskType;

        return $this;
    }

    /**
     * Set dateDue.
     *
     * @param \DateTime $dateDue
     *
     * @return $this
     */
    public function setDateDue($dateDue)
    {
        $this->date_due = $dateDue;

        return $this;
    }

    /**
     * Set dateEventStart.
     *
     * @param \DateTime $dateEventStart
     *
     * @return $this
     */
    public function setDateEventStart($dateEventStart)
    {
        $this->date_event_start = $dateEventStart;

        return $this;
    }

    /**
     * Set dateEventEnd.
     *
     * @param \DateTime $dateEventEnd
     *
     * @return $this
     */
    public function setDateEventEnd($dateEventEnd)
    {
        $this->date_event_end = $dateEventEnd;

        return $this;
    }

    /**
     * Set visibility.
     *
     * @param string $visibility
     *
     * @return $this
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Set urgency.
     *
     * @param int $urgency
     *
     * @return $this
     */
    public function setUrgency($urgency)
    {
        $this->urgency = $urgency;

        return $this;
    }

    /**
     * Set dateDone.
     *
     * @param \DateTime $dateDone
     *
     * @return $this
     */
    public function setDateDone($dateDone)
    {
        $this->date_done = $dateDone;

        return $this;
    }

    /**
     * Set displayOrder.
     *
     * @param int $displayOrder
     *
     * @return $this
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->display_order = $displayOrder;

        return $this;
    }

    /**
     * Set creator.
     *
     * @param Person $creator
     *
     * @return $this
     */
    public function setCreator(Person $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Set project.
     *
     * @param TaskProject $project
     *
     * @return $this
     */
    public function setProject(TaskProject $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Set list.
     *
     * @param TaskList $list
     *
     * @return $this
     */
    public function setList(TaskList $list = null)
    {
        $this->list = $list;

        return $this;
    }

    /**
     * Add subtask.
     *
     * @param TaskSubtask $subtask
     *
     * @return $this
     */
    public function addSubtask(TaskSubtask $subtask)
    {
        $this->subtasks[] = $subtask;

        return $this;
    }

    /**
     * Add label.
     *
     * @param LabelTask $label
     *
     * @return $this
     */
    public function addLabel(LabelTask $label)
    {
        $this->labels[] = $label;

        return $this;
    }

    /**
     * Remove label.
     *
     * @param LabelTask $label
     */
    public function removeLabel(LabelTask $label)
    {
        $this->labels->removeElement($label);
    }

    /**
     * Add comment.
     *
     * @param TaskComment $comment
     *
     * @return $this
     */
    public function addComment(TaskComment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Add attachment.
     *
     * @param TaskAttachment $attachment
     *
     * @return $this
     */
    public function addAttachment(TaskAttachment $attachment)
    {
        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * Add linkedArticle.
     *
     * @param TaskLinkedArticle $linkedArticle
     *
     * @return $this
     */
    public function addLinkedArticle(TaskLinkedArticle $linkedArticle)
    {
        $this->linked_articles[] = $linkedArticle;

        return $this;
    }

    /**
     * Add linkedChat.
     *
     * @param TaskLinkedChat $linkedChat
     *
     * @return $this
     */
    public function addLinkedChat(TaskLinkedChat $linkedChat)
    {
        $this->linked_chats[] = $linkedChat;

        return $this;
    }

    /**
     * Add linkedTicket.
     *
     * @param TaskLinkedTicket $linkedTicket
     *
     * @return $this
     */
    public function addLinkedTicket(TaskLinkedTicket $linkedTicket)
    {
        $this->linked_tickets[] = $linkedTicket;

        return $this;
    }

    /**
     * Get assigned.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssigned()
    {
        return $this->assigned;
    }

    /**
     * Set display order for a new task.
     *
     * @ORM\PrePersist
     *
     * @param LifecycleEventArgs $args
     */
    public function onSetDisplayOrder(LifecycleEventArgs $args)
    {
        if ($this->display_order) {
            return;
        }

        $qb = $args
            ->getObjectManager()
            ->getRepository(self::class)
            ->createQueryBuilder('t')
            ->select('MAX(t.display_order)')
        ;

        $this->display_order = (int) $qb->getQuery()->getSingleScalarResult() + 1;
    }

    /**
     * Re order display positions of related tasks.
     *
     * @ORM\PreUpdate
     *
     * @param PreUpdateEventArgs $args
     */
    public function onReOrderTasks(PreUpdateEventArgs $args)
    {
        if (!$args->hasChangedField('display_order')) {
            return;
        }

        $old_order = $args->getOldValue('display_order');
        $new_order = $args->getNewValue('display_order');

        if ($old_order !== $new_order) {
            $qb = $args->getEntityManager()->createQueryBuilder();
            $qb
                ->update()
                ->from(self::class, 't')
                ->set('t.display_order', sprintf('t.display_order + %d', ($new_order > $old_order ? -1 : 1)))
                ->where(
                    't.id != :task_id',
                    't.display_order > :min_order',
                    't.display_order <= :max_order'
                )
                ->setParameters([
                    'task_id'   => $this->getId(),
                    'min_order' => min($old_order, $new_order),
                    'max_order' => max($old_order, $new_order),
                ])
            ;

            $qb->getQuery()->execute();

            if ($old_order > $new_order) {
                ++$this->display_order;
            }
        }
    }
}
