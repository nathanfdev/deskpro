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
use DeskPRO\Bundle\AppBundle\Entity\ProjectMember;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\Entity\TaskProject as TaskProjectEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TaskProject.
 */
class TaskProject
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
     * Project title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * Departments - members of the project.
     *
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Department>>")
     *
     * @var Department[]
     */
    protected $departments;

    /**
     * Teams - members of the project.
     *
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\AgentTeam>>")
     *
     * @var AgentTeam[]
     */
    protected $teams;

    /**
     * Agents - members of the project.
     *
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\AgentTeam>>")
     *
     * @var Person[]
     */
    protected $agents;

    /**
     * Task remained to solve in the project.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $remaining;

    /**
     * TaskProject constructor.
     *
     * @param TaskProjectEntity $task_project
     */
    public function __construct(TaskProjectEntity $task_project)
    {
        $this->id    = $task_project->getId();
        $this->title = $task_project->getTitle();

        $this->calculateMembers($task_project);
    }

    protected function calculateMembers(TaskProjectEntity $entity)
    {

        /** @var ProjectMember[] $members */
        $members = $entity->getMembers();

        $groupedMembers = [
            'departments' => [],
            'teams'       => [],
            'agents'      => [],
        ];

        if (!empty($members)) {
            foreach ($members as $member) {
                if (!empty($member->getDepartment())) {
                    $groupedMembers['departments'][] = $member->getDepartment();
                } elseif (!empty($member->getTeam())) {
                    $groupedMembers['teams'][] = $member->getTeam();
                } else {
                    $groupedMembers['agents'][] = $member->getPerson();
                }
            }
        }

        /** @var Task[] $tasks */
        $tasks = $entity->getTasks();

        $remaining = 0;
        foreach ($tasks as $task) {
            if (!$task->isDone()) {
                ++$remaining;
            }
        }

        $this->departments = $groupedMembers['departments'];
        $this->teams       = $groupedMembers['teams'];
        $this->agents      = $groupedMembers['agents'];
        $this->remaining   = $remaining;
    }
}
