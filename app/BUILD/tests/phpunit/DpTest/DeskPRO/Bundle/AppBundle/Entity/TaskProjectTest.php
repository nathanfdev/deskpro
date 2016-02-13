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

use DeskPRO\Bundle\AppBundle\Entity\TaskProject;
use DpTest\ApiTestCase;

class TaskProjectTest extends ApiTestCase
{
    /**
     * Test that a valid project can be saved.
     */
    public function testValid()
    {
        $validator = $this->getValidator();

        // A valid task only needs a title and to have a person injected
        $task = new TaskProject();
        $task->setTitle('A test project');

        $errors = $validator->validate($task);

        $this->assertEquals(0, count($errors));
    }

    /**
     * Test what happens when an invalid project is saved.
     */
    public function testInvalidTitle()
    {
        $validator = $this->getValidator();
        $task      = new TaskProject();
        // Don't set a title

        $errors = $validator->validate($task);

        $this->assertGreaterThan(0, count($errors));
        $constraint = $errors[0]->getConstraint();

        $this->assertEquals('title', $errors[0]->getPropertyPath());
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $constraint);
    }
}
