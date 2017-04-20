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

namespace DeskPRO\Component\TaskRunner\Processor;

use DeskPRO\Component\TaskRunner\Task\Task;
use DeskPRO\Component\TaskRunner\Task\TaskInterface;
use DeskPRO\Component\TaskRunner\TaskHandle;
use DeskPRO\Component\Util\Buffer\LineBuffer;
use InvalidArgumentException;
use Monolog\Logger;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;

abstract class AbstractCommandProcessor implements ProcessorInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param TaskInterface $task
     * @param LoopInterface $loop
     *
     * @return TaskHandle
     */
    public function start(TaskInterface $task, LoopInterface $loop)
    {
        if (!$task instanceof Task) {
            throw new InvalidArgumentException();
        }

        $cmd  = $this->getCmdString($task);
        $proc = new Process($cmd);
        $proc->start($loop);
        $this->attachProcLoggers($task, $proc);

        $handle = new TaskHandle($task, $proc);
        $proc->on('exit', function ($code) use ($handle) {
            if ((int) $code !== 0) {
                $handle->setFailed();
            } else {
                $handle->setSuccess();
            }
        });

        return $handle;
    }

    /**
     * @param Task $task
     *
     * @return Process
     */
    protected function attachProcLoggers(Task $task, Process $process)
    {
        list($out_buf, $err_buf) = $this->createLineBuffers($task);

        $process->stdout->on('data', function ($output) use ($out_buf) {
            $out_buf->append($output);
        });
        $process->stderr->on('data', function ($output) use ($err_buf) {
            $err_buf->append($output);
        });
        $process->on('exit', function () use ($out_buf, $err_buf) {
            $err_buf->flush();
            $out_buf->flush();
        });
    }

    /**
     * @param Task $task
     *
     * @return string
     */
    abstract protected function getCmdString(Task $task);

    /**
     * @param $task
     *
     * @return LineBuffer[]
     */
    protected function createLineBuffers(Task $task)
    {
        $logger  = $this->logger;
        $out_buf = new LineBuffer(function ($line) use ($task, $logger) {
            $logger->debug($line, [
                'task' => $task,
            ]);
        });
        $err_buf = new LineBuffer(function ($line) use ($task, $logger) {
            $logger->error('[ERR] '.$line, [
                'task' => $task,
            ]);
        });

        return [$out_buf, $err_buf];
    }

    /**
     * @param TaskHandle $taskHandle
     */
    public function terminate(TaskHandle $taskHandle)
    {
        /** @var Process $proc */
        $proc = $taskHandle->getHandle();
        $proc->terminate();
    }

    /**
     * @param TaskHandle $taskHandle
     *
     * @return string
     */
    public function status(TaskHandle $taskHandle)
    {
        /** @var Process $proc */
        $proc = $taskHandle->getHandle();

        if ($proc->isRunning()) {
            return ProcessorInterface::STATUS_RUNNING;
        } else {
            return ProcessorInterface::STATUS_STOPPED;
        }
    }
}
