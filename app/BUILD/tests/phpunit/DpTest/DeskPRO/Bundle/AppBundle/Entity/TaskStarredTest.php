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

namespace DpTest\DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\Entity\TaskStarred;
use DpTest\ApiTestCase;

class TaskStarredTest extends ApiTestCase
{
    /**
     * Test that a valid star is saved.
     */
    public function testValid()
    {
        $validator = $this->getValidator();

        $starred = new TaskStarred();
        $starred->setPerson($this->getUser());
        $starred->setTask($this->getTask());

        $errors = $validator->validate($starred);

        $this->assertEquals(0, count($errors));
    }

    /**
     * Test what happens when no person is attached.
     */
    public function testInvalidPerson()
    {
        $validator = $this->getValidator();

        $starred = new TaskStarred();
        $starred->setTask($this->getTask());

        $errors = $validator->validate($starred);

        $this->assertGreaterThan(0, count($errors));
        $constraint = $errors[0]->getConstraint();

        $this->assertEquals('person', $errors[0]->getPropertyPath());
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotNull', $constraint);
    }

    /**
     * Test what happens when no task is attached.
     */
    public function testInvalidTask()
    {
        $validator = $this->getValidator();

        $starred = new TaskStarred();
        $starred->setPerson($this->getUser());

        $errors = $validator->validate($starred);

        $this->assertGreaterThan(0, count($errors));
        $constraint = $errors[0]->getConstraint();

        $this->assertEquals('task', $errors[0]->getPropertyPath());
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotNull', $constraint);
    }

    /**
     * Get an example person.
     *
     * @return mixed
     */
    private function getUser()
    {
        $person = new Person();
        $person->setEmail('example@example.com');
        $person->setName('Test User');

        return $person;
    }

    /**
     * Get an example task.
     *
     * @return Task
     */
    private function getTask()
    {
        $task = new Task();
        $task->setCreator($this->getUser());
        $task->setTitle('test task');

        return $task;
    }
}
