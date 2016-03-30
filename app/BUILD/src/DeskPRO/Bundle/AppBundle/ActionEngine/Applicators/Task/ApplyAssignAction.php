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
 */

namespace DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\Task;

use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\AbstractActionApplicator;
use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\ActionApplicatorInterface;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\Entity\TaskAssignment;

class ApplyAssignAction extends AbstractActionApplicator implements ActionApplicatorInterface
{
    /**
     * @param Task[] $tasks
     */
    public function apply(array $tasks)
    {
        $collection = $this->init($tasks);

        foreach ($collection as $type => $values) {
            switch ($type) {
                case 'agents':
                    foreach ($values as $person) {
                        foreach ($tasks as $task) {
                            $assignment = new TaskAssignment();
                            $assignment->setPerson($person);
                            $assignment->setTask($task);
                            $this->em->persist($assignment);
                            $task->addAssigned($assignment);
                        }
                    }
                    break;
                case 'teams':
                    foreach ($values as $team) {
                        foreach ($tasks as $task) {
                            $assignment = new TaskAssignment();
                            $assignment->setTeam($team);
                            $assignment->setTask($task);
                            $this->em->persist($assignment);
                            $task->addAssigned($assignment);
                        }
                    }
                    break;
                case 'departments':
                    foreach ($values as $department) {
                        foreach ($tasks as $task) {
                            $assignment = new TaskAssignment();
                            $assignment->setDepartment($department);
                            $assignment->setTask($task);
                            $this->em->persist($assignment);
                            $task->addAssigned($assignment);
                        }
                    }
                    break;
            }
        }
    }

    /**
     * @param Task[] $tasks
     *
     * @return array
     */
    private function init($tasks)
    {
        foreach ($tasks as $task) {
            foreach ($task->getAssigned() as $assigned) {
                $this->em->remove($assigned);
            }
        }
        $collection = [
            'agents'      => [],
            'teams'       => [],
            'departments' => [],
        ];
        foreach ($this->options as $type => $values) {
            switch ($type) {
                case 'agent':
                    foreach ($values as $id) {
                        $collection['agents'][] = $this->em->getRepository('DeskPRO:Person')->find($id);
                    }
                    break;
                case 'team':
                    foreach ($values as $id) {
                        $collection['teams'][] = $this->em->getRepository('DeskPRO:AgentTeam')->find($id);
                    }
                    break;
                case 'department':
                    foreach ($values as $id) {
                        $collection['departments'][] = $this->em->getRepository('DeskPRO:Department')->find($id);
                    }
                    break;
            }
        }

        return $collection;
    }
}
