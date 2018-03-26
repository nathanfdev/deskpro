<?php

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
