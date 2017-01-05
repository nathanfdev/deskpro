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
