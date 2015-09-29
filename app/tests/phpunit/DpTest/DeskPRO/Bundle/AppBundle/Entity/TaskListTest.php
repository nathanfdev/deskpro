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
namespace DpTest\DeskPRO\Bundle\AppBundle\Entity;

use DeskPRO\Bundle\AppBundle\Entity\TaskList;
use DeskPRO\Bundle\AppBundle\Entity\TaskProject;
use DpTest\ApiTestCase;

class TaskListTest extends ApiTestCase
{
    /**
     * Test valid project member.
     */
    public function testValid()
    {
        $project   = $this->getValidProject();
        $validator = $this->getValidator();

        $task_list = new TaskList();
        $task_list->setProject($project);
        $task_list->setTitle('test title');

        $errors = $validator->validate($task_list);

        $this->assertEquals(0, count($errors));
    }

    /**
     * Test what happens when an invalid project is saved.
     */
    public function testInvalidProject()
    {
        $project   = $this->getInvalidProject();
        $validator = $this->getValidator();

        $task_list = new TaskList();
        $task_list->setProject($project);
        $task_list->setTitle('test title');

        $errors = $validator->validate($task_list);

        $this->assertGreaterThan(0, count($errors));
        $constraint = $errors[0]->getConstraint();

        $this->assertEquals('project.title', $errors[0]->getPropertyPath());
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $constraint);
    }

    /**
     * Test what happens when no title is saved.
     */
    public function testInvalidTitle()
    {
        $project   = $this->getInvalidProject();
        $validator = $this->getValidator();

        $task_list = new TaskList();
        $task_list->setProject($project);

        $errors = $validator->validate($task_list);

        $this->assertGreaterThan(0, count($errors));
        $constraint = $errors[0]->getConstraint();

        $this->assertEquals('title', $errors[0]->getPropertyPath());
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $constraint);
    }

    /**
     * Get an example valid task.
     *
     * @return TaskProject
     */
    private function getValidProject()
    {
        $project = new TaskProject();
        $project->setTitle('valid project');

        return $project;
    }

    /**
     * @return TaskProject
     */
    private function getInvalidProject()
    {
        return new TaskProject();
    }
}
