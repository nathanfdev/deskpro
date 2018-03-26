<?php

namespace DeskPRO\Component\TaskRunner\Processor;

use DeskPRO\Component\TaskRunner\Task\TaskInterface;
use DeskPRO\Component\TaskRunner\TaskHandle;
use React\EventLoop\LoopInterface;

interface ProcessorInterface
{
    const STATUS_RUNNING = 'running';
    const STATUS_STOPPED = 'stopped';

    /**
     * @param TaskInterface $task
     * @param LoopInterface $loop
     *
     * @return mixed
     */
    public function start(TaskInterface $task, LoopInterface $loop);

    /**
     * @param TaskHandle $taskHandle
     */
    public function terminate(TaskHandle $taskHandle);

    /**
     * @param TaskHandle $taskHandle
     *
     * @return string
     */
    public function status(TaskHandle $taskHandle);
}
