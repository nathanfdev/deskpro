<?php

namespace DeskPRO\Component\TaskRunner\Processor;

use DeskPRO\Component\TaskRunner\Task\Task;
use DeskPRO\Component\TaskRunner\Task\TaskInterface;
use DeskPRO\Component\TaskRunner\TaskHandle;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;

/**
 * This is a blocking processor so it's not very useful except for tests.
 */
class TaskCallbackProcessor implements ProcessorInterface
{
    /**
     * Array of ID => Promise.
     *
     * @var array
     */
    private $running = [];

    /**
     * @internal
     *
     * @param TaskInterface $task
     */
    public function _markDone(TaskInterface $task)
    {
        unset($this->running[$task->getId()]);
    }

    /**
     * @param TaskInterface $task
     * @param LoopInterface $loop
     *
     * @return TaskHandle
     */
    public function start(TaskInterface $task, LoopInterface $loop)
    {
        if ($task instanceof Task && $task->has('callback')) {
            $fn = $task->get('callback');
        } else {
            $fn = function () {
                return 1;
            };
        }

        $resolver = function ($resolve, $reject) use ($fn) {
            try {
                $resolve($fn());
            } catch (\Exception $e) {
                $reject($e);
            }
        };

        $me = $this;

        $d = new Promise($resolver);
        $d->then(function ($v) use ($task, $me) {
            $me->_markDone($task);

            return $v;
        }, function ($v) use ($task, $me) {
            $me->_markDone($task);

            return $v;
        });

        $handle = new TaskHandle($task, $d);

        return $handle;
    }

    /**
     * @param TaskHandle $taskHandle
     */
    public function terminate(TaskHandle $taskHandle)
    {
        /** @var Promise $h */
        $h = $taskHandle->getHandle();
        $h->cancel();
    }

    /**
     * @param TaskHandle $taskHandle
     *
     * @return string
     */
    public function status(TaskHandle $taskHandle)
    {
        if (isset($this->running[$taskHandle->getTask()->getId()])) {
            return ProcessorInterface::STATUS_RUNNING;
        }

        return ProcessorInterface::STATUS_STOPPED;
    }
}
