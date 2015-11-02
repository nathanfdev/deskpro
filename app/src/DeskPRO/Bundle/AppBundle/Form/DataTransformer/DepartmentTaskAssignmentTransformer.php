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
 */
namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Bundle\AppBundle\Entity\LabelTask;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\Entity\TaskAssignment;
use Symfony\Component\Form\DataTransformerInterface;

class DepartmentTaskAssignmentTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Task
     */
    private $task;

    /**
     * Constructor.
     *
     * @param EntityManager $entityManager
     * @param Task          $task
     */
    public function __construct(EntityManager $entityManager, Task $task = null)
    {
        $this->entityManager = $entityManager;
        $this->task          = $task;
    }

    /**
     * Transform a Task Assignment into a department.
     *
     * @param mixed $memberObject
     */
    public function transform($memberObject)
    {
        if (!is_null($memberObject) && ($memberObject instanceof TaskAssignment)) {
            return $memberObject->getDepartment()->getId();
        }

        return;
    }

    /**
     * Transform a department entity into a Task Assignment.
     *
     * @param string $department
     *
     * @return LabelTask|null|object
     */
    public function reverseTransform($department)
    {
        if (!$this->task) {
            return;
        }

        if (!$department instanceof Department) {
            $department = $this->entityManager->getRepository('DeskPRO:Department')
                ->find($department);
        }
        $member = $this->entityManager->getRepository('App:TaskAssignment')
            ->findOneBy(array('department' => $department, 'task' => $this->task));

        if (!$member) {
            $member = new TaskAssignment();
            $member->setDepartment($department);
            $member->setTask($this->task);
        }

        return $department;
    }
}
