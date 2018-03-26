<?php

namespace DpUnitTests\DeskPRO\WorkerProcess;

use Application\DeskPRO\Log\Logger;
use Application\DeskPRO\WorkerProcess\Job\AbstractJob;

class JobTimeTest extends \PHPUnit_Framework_TestCase
{
    public function testOptions()
    {
        $time  = time() - 1000;
        $limit = 10;

        $j = new TestJob($time, $limit);
        $this->assertEquals($time, $j->getStartTime());
        $this->assertEquals($limit, $j->getTimeLimit());

        $j = new TestJob(time(), 0);
        $this->assertNull($j->getTimeLimit());

        $j = new TestJob(time(), -1);
        $this->assertNull($j->getTimeLimit());

        $j = new TestJob(time(), null);
        $this->assertNull($j->getTimeLimit());
    }

    public function testOkTimeLimit()
    {
        $j = new TestJob(time() - 5, 10);
        $this->assertFalse($j->isPastTimeLimit());

        // exact
        $j = new TestJob(time() - 10, 10);
        $this->assertFalse($j->isPastTimeLimit());

        // no time limit
        $j = new TestJob(time() - 500, null);
        $this->assertFalse($j->isPastTimeLimit());
    }

    public function testFailTimeLimit()
    {
        $j = new TestJob(time() - 60, 10);
        $this->assertTrue($j->isPastTimeLimit());

        $j = new TestJob(time() - 11, 10);
        $this->assertTrue($j->isPastTimeLimit());
    }

    public function testRemainTime()
    {
        $j = new TestJob(time() - 5, 10);
        $this->assertEquals(5, $j->getRemainingTime());

        $j = new TestJob(time() - 9, 10);
        $this->assertEquals(1, $j->getRemainingTime());

        // no time limit
        $j = new TestJob(time() - 500, null);
        $this->assertNull($j->getRemainingTime());
    }

    public function testRemainZeroTime()
    {
        $j = new TestJob(time() - 11, 10);
        $this->assertEquals(0, $j->getRemainingTime());

        $j = new TestJob(time() - 10, 10);
        $this->assertEquals(0, $j->getRemainingTime());

        $j = new TestJob(time() - 100, 10);
        $this->assertEquals(0, $j->getRemainingTime());
    }
}

class TestJob extends AbstractJob
{
    public function __construct($startTs, $timeLimit)
    {
        parent::__construct(new Logger(), ['time_limit' => $timeLimit]);
        $this->startTs = $startTs;
    }

    public function run()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
        throw new \RuntimeException('Not implemented');
    }
}
