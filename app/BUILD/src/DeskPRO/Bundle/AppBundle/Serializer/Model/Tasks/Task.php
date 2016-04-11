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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tasks;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Task as TaskEntity;
use DeskPRO\Bundle\AppBundle\Entity\TaskList;
use DeskPRO\Bundle\AppBundle\Entity\TaskProject as TaskProjectEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Task.
 */
class Task
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * Task title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * Task title.
     *
     * @JMS\Type("array<to_string<DeskPRO\Bundle\AppBundle\Entity\LabelTask>>")
     *
     * @var string
     */
    protected $labels;

    /**
     * Complete or incomplete.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_done;

    /**
     * Task progress.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $percent_complete;

    /**
     * Date when task was created.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Task type.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $task_type;

    /**
     * Date due which task should be completed.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_due;

    /**
     * Date when event was started.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_event_start;

    /**
     * Date when event was ended.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_event_end;

    /**
     * Person created this task.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $creator;

    /**
     * Where task could be seen: public, private or project.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $visibility;

    /**
     * Project this task belongs to.
     *
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\TaskProject>")
     *
     * @var TaskProjectEntity
     */
    protected $project;

    /**
     * List this task belongs to.
     *
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\TaskList>")
     *
     * @var TaskList
     */
    protected $list;

    /**
     * Task urgency.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $urgency;

    /**
     * Tickets are linked with this task.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Ticket>>")
     *
     * @var ArrayCollection
     */
    protected $linked_tickets;

    /**
     * Chats are linked with this task.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\ChatConversation>>")
     *
     * @var ArrayCollection
     */
    protected $linked_chats;

    /**
     * Articles are linked with this task.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Article>>")
     *
     * @var ArrayCollection
     */
    protected $linked_articles;

    /**
     * Date when task was done.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_done;

    /**
     * Task display order.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $display_order;

    /**
     * Agents assigned to this task.
     *
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Person>>")
     *
     * @var Person[]
     */
    protected $agents = [];

    /**
     * Departments assigned to this task.
     *
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Department>>")
     *
     * @var Department[]
     */
    protected $departments = [];

    /**
     * Teams assigned to this task.
     *
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\AgentTeam>>")
     *
     * @var AgentTeam[]
     */
    protected $teams = [];

    /**
     * Total comments count for this task.
     *
     * @JMS\Type("deferred<integer>")
     *
     * @var CallbackDeferredProperty
     */
    protected $comment_count;

    /**
     * Total subtasks count for this task.
     *
     * @JMS\Type("deferred<integer>")
     *
     * @var CallbackDeferredProperty
     */
    protected $subtasks_total;

    /**
     * Total completed subtasks count for this task.
     *
     * @JMS\Type("deferred<integer>")
     *
     * @var CallbackDeferredProperty
     */
    protected $subtasks_done;

    /**
     * Task constructor.
     *
     * @param TaskEntity               $task
     * @param CallbackDeferredProperty $comment_count
     * @param CallbackDeferredProperty $subtasks_total
     * @param CallbackDeferredProperty $subtasks_done
     */
    public function __construct(
        TaskEntity $task,
        CallbackDeferredProperty $comment_count,
        CallbackDeferredProperty $subtasks_total,
        CallbackDeferredProperty $subtasks_done
    ) {
        $this->id               = $task->getId();
        $this->title            = $task->getTitle();
        $this->labels           = $task->getLabels();
        $this->is_done          = $task->isDone();
        $this->percent_complete = $task->getPercentComplete();
        $this->date_created     = $task->getDateCreated();
        $this->task_type        = $task->getTaskType();
        $this->date_due         = $task->getDateDue();
        $this->date_event_start = $task->getDateEventStart();
        $this->date_event_end   = $task->getDateEventEnd();
        $this->creator          = $task->getCreator();
        $this->visibility       = $task->getVisibility();
        $this->project          = $task->getProject();
        $this->list             = $task->getList();
        $this->urgency          = $task->getUrgency();

        $this->linked_tickets = $task->getLinkedTickets()
            ? $task->getLinkedTickets()->map(function ($item) {return $item->getTicket();})
            : null;

        $this->linked_chats = $task->getLinkedChats()
            ? $task->getLinkedChats()->map(function ($item) {return $item->getChat();})
            : null;

        $this->linked_articles = $task->getLinkedArticles()
            ? $task->getLinkedArticles()->map(function ($item) {return $item->getArticle();})
            : null;

        $this->date_done      = $task->getDateDone();
        $this->display_order  = $task->getDisplayOrder();
        $this->comment_count  = $comment_count;
        $this->subtasks_total = $subtasks_total;
        $this->subtasks_done  = $subtasks_done;

        $this->calculateAssignee($task);
    }

    /**
     * @param TaskEntity $task
     */
    protected function calculateAssignee(TaskEntity $task)
    {
        $assignees = $task->getAssigned();

        if (!empty($assignees)) {
            foreach ($assignees as $assigned) {
                if (!empty($assigned->getDepartment())) {
                    $this->departments[] = $assigned->getDepartment();
                } elseif (!empty($assigned->getTeam())) {
                    $this->teams[] = $assigned->getTeam();
                } else {
                    $this->agents[] = $assigned->getPerson();
                }
            }
        }
    }
}
