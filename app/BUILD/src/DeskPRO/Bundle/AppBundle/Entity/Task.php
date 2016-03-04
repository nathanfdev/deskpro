<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\TaskRepository")
 * @ORM\Table(name="tasks_new")
 * @ORM\HasLifecycleCallbacks
 */
class Task implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

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
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedArticle", mappedBy="task")
     */
    protected $linked_articles;

    /**
     * @var TaskLinkedChat[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedChat", mappedBy="task")
     */
    protected $linked_chats;

    /**
     * @var TaskLinkedTicket[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedTicket", mappedBy="task")
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
     * @param Person $creator
     */
    public function __construct(Person $creator)
    {
        $this->subtasks = new ArrayCollection();
        $this->labels   = new ArrayCollection();
        $this->assigned = new ArrayCollection();
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
     * @return mixed
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
        $this->setModelField('title', $title);
    }

    /**
     * @param bool $is_done
     */
    public function setIsDone($is_done = true)
    {
        $date_done = $is_done ? new \DateTime() : null;

        $this->setDateDone($date_done);
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
     * @param \DateTime|null $date_done
     */
    public function setDateDone($date_done)
    {
        $this->setModelField('date_done', $date_done);
    }

    /**
     * @param int $display_order
     */
    public function setDisplayOrder($display_order)
    {
        $this->setModelField('display_order', $display_order);
    }

    /**
     * @param TaskSubtask $subtask
     */
    public function addSubtask(TaskSubtask $subtask)
    {
        $this->subtasks->add($subtask);
    }

    /**
     * @param LabelTask $label
     */
    public function addLabel(LabelTask $label)
    {
        $this->labels->add($label);
    }

    /**
     * @param LabelTask $label
     */
    public function removeLabel(LabelTask $label)
    {
        $this->labels->removeElement($label);
    }

    /**
     * @param TaskComment $comment
     */
    public function addComment(TaskComment $comment)
    {
        $this->comments->add($comment);
    }

    /**
     * @param TaskAttachment $attachment
     */
    public function addAttachment(TaskAttachment $attachment)
    {
        $this->attachments->add($attachment);
    }

    /**
     * @param TaskLinkedArticle $linked_article
     */
    public function addLinkedArticle(TaskLinkedArticle $linked_article)
    {
        $this->linked_articles->add($linked_article);
    }

    /**
     * @param TaskLinkedChat $linked_chat
     */
    public function addLinkedChat(TaskLinkedChat $linked_chat)
    {
        $this->linked_chats->add($linked_chat);
    }

    /**
     * @param TaskLinkedTicket $linked_ticket
     */
    public function addLinkedTicket(TaskLinkedTicket $linked_ticket)
    {
        $this->linked_tickets->add($linked_ticket);
    }

    /**
     * @return TaskAssignment[]|ArrayCollection
     */
    public function getAssigned()
    {
        return $this->assigned;
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
        $this->setModelField('assignment', $assignment);
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
     * Re order display positions of related tasks.
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
            ->getRepository('App:Task')
            ->createQueryBuilder('t')
            ->select('MAX(t.display_order)');

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
                ->from('App:Task', 't')
                ->set('t.display_order', sprintf('t.display_order + %d', ($new_order > $old_order ? -1 : 1)))
                ->where(
                    't.id != :task_id',
                    't.display_order > :min_order',
                    't.display_order <= :max_order'
                )
                ->setParameters(
                    [
                        'task_id'   => $this->getId(),
                        'min_order' => min($old_order, $new_order),
                        'max_order' => max($old_order, $new_order),
                    ]
                );

            $qb->getQuery()->execute();

            if ($old_order > $new_order) {
                ++$this->display_order;
            }
        }
    }
}
