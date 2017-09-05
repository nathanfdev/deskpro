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
