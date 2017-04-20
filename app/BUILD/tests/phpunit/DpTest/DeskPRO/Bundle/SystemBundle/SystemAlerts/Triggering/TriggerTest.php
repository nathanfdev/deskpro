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

/**
 * DeskPRO.
 */

namespace DpTest\Bundle\SystemBundle\SystemAlerts;

use DpTest\DeskProTestCase;

require_once realpath(__DIR__.'/../_mocks.php');

/**
 * Class TriggerTest.
 */
class TriggerTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(MockTrigger::class, new MockTrigger());
    }

    /**
     * @test
     */
    public function it_should_call_raised_callback_when_incident_is_raised()
    {
        $called_times = 0;
        $trigger      = new MockTrigger();
        $trigger->setRaisedCallback(function () use (&$called_times) {
            ++$called_times;
        });

        for ($i = 0; $i < 5; ++$i) {
            $trigger->consume(new MockEvent(false, 'test'));
        }

        $this->assertEquals(1, $called_times);
    }

    /**
     * @test
     */
    public function it_should_call_raised_callback_for_every_raised_incident()
    {
        $called_times = 0;
        $trigger      = new MockTrigger();
        $trigger->setRaisedCallback(function () use (&$called_times) {
            ++$called_times;
        });

        for ($i = 0; $i < 17; ++$i) {
            $trigger->consume(new MockEvent(false, 'test'));
        }

        $this->assertEquals(3, $called_times);
    }
}
