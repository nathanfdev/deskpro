<?php

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
