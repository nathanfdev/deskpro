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

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\LabelTask;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DpTest\ApiTestCase;

class LabelTaskTest extends ApiTestCase
{
    /**
     * Test that a valid label can be saved.
     */
    public function testValid()
    {
        $task      = $this->getValidTask();
        $validator = $this->getValidator();

        $label = new LabelTask();
        $label->setTask($task);
        $label->setLabel('test');

        $errors = $validator->validate($label);

        $this->assertEquals(0, count($errors));
    }

    /**
     * Test what happens when an invalid task is saved.
     */
    public function testInvalidTitle()
    {
        $task      = $this->getInvalidTask();
        $validator = $this->getValidator();

        $label = new LabelTask();
        $label->setTask($task);
        $label->setLabel('test');

        $errors = $validator->validate($label);

        $this->assertGreaterThan(0, count($errors));
        $constraint = $errors[0]->getConstraint();

        $this->assertEquals('task.title', $errors[0]->getPropertyPath());
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $constraint);
    }

    /**
     * Test what happens if an invalid label is saved.
     */
    public function testInvalidLabel()
    {
        $task      = $this->getValidTask();
        $validator = $this->getValidator();

        $label = new LabelTask();
        $label->setTask($task);

        $errors = $validator->validate($label);

        $this->assertGreaterThan(0, count($errors));
        $constraint = $errors[0]->getConstraint();

        $this->assertEquals('label', $errors[0]->getPropertyPath());
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $constraint);
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
     * Get an example valid task.
     *
     * @return Task
     */
    private function getValidTask()
    {
        $task = new Task($this->getUser());
        $task->setTitle('valid task');

        return $task;
    }

    /**
     * @return Task
     */
    private function getInvalidTask()
    {
        return new Task($this->getUser());
    }
}
