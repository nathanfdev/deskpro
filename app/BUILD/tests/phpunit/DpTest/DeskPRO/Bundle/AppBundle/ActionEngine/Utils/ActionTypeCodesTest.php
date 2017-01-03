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

namespace DpTest\DeskPRO\Bundle\AppBundle\ActionEngine\Utils;

use DeskPRO\Bundle\AppBundle\ActionEngine\Utils\ActionTypeCodes;
use DpTest\DeskProTestCase;

class ActionTypeCodesTest extends DeskProTestCase
{
    public static $serializedArray = [
        'name'    => 'set_type',
        'options' => ['id' => 1],
    ];

    public static $wrongSerializedArray = [
        'name'    => 'something',
        'options' => ['id' => 1],
    ];

    /**
     * @test
     */
    public function actionToApplicatorClassName_should_return_string()
    {
        $applicatorClass =
            ActionTypeCodes::getActionApplicatorClassForActionName('Feedback', self::$serializedArray['name']);
        $this->assertStringStartsWith('DeskPRO\\Bundle\\AppBundle\\ActionEngine\\Applicators\\', $applicatorClass);
        $this->assertStringEndsWith('Action', $applicatorClass);
    }

    /**
     * @test
     * @expectedException \DeskPRO\Bundle\AppBundle\ActionEngine\Exception\ActionApplicatorDoesNotExists
     */
    public function actionToApplicatorClassName_should_raise_ActionApplicatorDoesNotExists_on_wrong_action()
    {
        return ActionTypeCodes::getActionApplicatorClassForActionName('Feedback', self::$wrongSerializedArray['name']);
    }
}
