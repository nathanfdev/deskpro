<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\Task as TaskEntity;
use JMS\Serializer\Annotation as JMS;

class Task
{
    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * Whether this task is completed.
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isCompleted;

    /**
     * The task's title.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * The task's visibility. On of: self::PRIVATE_VISIBILITY(0) or self::PUBLIC_VISIBILITY(1).
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $visibility;

    /**
     * The task's optional due date.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateDue = null;

    /**
     * The date the task was inserted into the system.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * The date the task was completed.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateCompleted;

    /**
     * The person created this task.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    private $person;

    /**
     * The person assigned to complete this task.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    private $assignedAgent;

    /**
     * The department assigned to complete this task.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Department>")
     *
     * @var \Application\DeskPRO\Entity\Department
     */
    private $assignedDepartment;

    /**
     * The agent team assigned to complete this task.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\AgentTeam>")
     *
     * @var \Application\DeskPRO\Entity\AgentTeam
     */
    private $assignedAgentTeam;

    /**
     * Labels associated with this task.
     *
     * @JMS\Expose()
     * @JMS\Type("array<to_string<Application\DeskPRO\Entity\LabelTask>>")
     */
    private $labels;

    public function __construct(TaskEntity $task)
    {
        $this->id                 = $task->getId();
        $this->isCompleted        = $task->getIsCompleted();
        $this->title              = $task->getTitle();
        $this->visibility         = $task->getVisibility();
        $this->dateDue            = $task->getDateDue();
        $this->dateCreated        = $task->getDateCreated();
        $this->dateCompleted      = $task->getDateCompleted();
        $this->person             = $task->getPerson();
        $this->assignedAgent      = $task->getAssignedAgent();
        $this->assignedDepartment = $task->getAssignedDepartment();
        $this->assignedAgentTeam  = $task->getAssignedAgentTeam();
        $this->labels             = $task->getLabels();
    }
}
