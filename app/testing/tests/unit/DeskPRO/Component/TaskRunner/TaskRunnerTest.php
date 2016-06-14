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

namespace DpUnitTests\DeskPRO\Component\TaskRunner;

use DeskPRO\Component\TaskRunner\Processor\CallbackProcessor;
use DeskPRO\Component\TaskRunner\Processor\TaskCallbackProcessor;
use DeskPRO\Component\TaskRunner\Reader\ArrayReader;
use DeskPRO\Component\TaskRunner\Reader\RedisReader;
use DeskPRO\Component\TaskRunner\Task\CallbackTaskFactory;
use DeskPRO\Component\TaskRunner\Task\Task;
use DeskPRO\Component\TaskRunner\TaskRunner;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class TaskRunnerTest extends \DpUnitTestCase
{
    public function testTaskRunner()
    {
        $logger = new Logger('runner');
        $logger->pushHandler(new StreamHandler(STDOUT, Logger::WARNING));

        $got = array();

        $reader = new ArrayReader();
        for ($i = 0; $i < 100; ++$i) {
            $reader->add(array('callback' => function () use ($i, &$got) {
                $got[] = 2;
            }));
        }

        $proc = new TaskCallbackProcessor();

        $runner = new TaskRunner(array(
            'logger'       => $logger,
            'reader'       => $reader,
            'processor'    => $proc,
            'max_tasks'    => 5,
            'tick_time'    => 0.001,
            'stop_on_done' => true,
        ));

        $runner->start();

        $this->assertEquals(5, $runner->getPeakTasks(), 'Peak tasks');
        $this->assertEquals(100, count($got), 'Number of callbacks');
        $this->assertEquals(200, array_sum($got), 'Value of callbacks');
        $this->assertEquals(0, $reader->count(), 'Remaining tasks');
    }

    public function testTaskRunnerWithRedisReader()
    {
        $logger = new Logger('runner');
        $logger->pushHandler(new StreamHandler(STDOUT, Logger::WARNING));

        $got = array();

        $reader = new ArrayReader();
        for ($i = 0; $i < 100; ++$i) {
            $reader->add(array('payload' => json_encode(array('num' => 2))));
        }

        $client = \Mockery::mock('Predis\Client');
        $client->shouldReceive('isConnected')->andReturn(true);
        $client->shouldReceive('rpop')->andReturnUsing(function () use ($reader) {
            return $reader->getNext();
        });

        // Mimic real-life case where we'd decode with json
        $factory = new CallbackTaskFactory(function (Task $n) {
            $n = json_decode($n->get('payload'), true);
            $task = new Task($n);

            return $task;
        });

        $proc = new CallbackProcessor(function (Task $task) use (&$got) {
            $got[] = $task->get('num');
        });

        $redis_reader = new RedisReader(array(
            'redis_client' => $client,
            'redis_key'    => 'bogus',
            'logger'       => $logger,
            'task_factory' => $factory,
        ));

        $runner = new TaskRunner(array(
            'logger'       => $logger,
            'reader'       => $redis_reader,
            'processor'    => $proc,
            'max_tasks'    => 5,
            'tick_time'    => 0.001,
            'stop_on_done' => true,
        ));

        $runner->start();

        $this->assertEquals(5, $runner->getPeakTasks(), 'Peak tasks');
        $this->assertEquals(100, count($got), 'Number of callbacks');
        $this->assertEquals(200, array_sum($got), 'Value of callbacks');
        $this->assertEquals(0, $reader->count(), 'Remaining tasks');
    }
}
