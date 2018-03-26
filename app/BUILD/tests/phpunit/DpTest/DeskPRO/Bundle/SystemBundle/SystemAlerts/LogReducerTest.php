<?php

/**
 * DeskPRO.
 */

namespace DpTest\Bundle\SystemBundle\SystemAlerts;

use Application\DeskPRO\DBAL\Connection;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\LogReducer;
use DpTest\DeskProTestCase;

/**
 * Class LogReducerTest.
 */
class LogReducerTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(LogReducer::class, $this->instance());
    }

    /**
     * @return LogReducer
     */
    private function instance()
    {
        return new LogReducer($this->mockConnection(Connection::class)->reveal());
    }
}
