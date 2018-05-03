<?php

namespace DeskPRO\Component\TaskRunner;

use DeskPRO\Component\TaskRunner\Task\TaskInterface;

/**
 * Once a task is started, the processor should return a handle
 * that can be used to interact with the task later (e.g., check its state, terminate it, etc).
 */
class TaskHandle
{
    const EVENT_DONE_SUCCESS = 'done.success';
    const EVENT_DONE_FAILURE = 'done.failure';

    /**
     * @var TaskInterface
     */
    private $task;

    /**
     * @var mixed
     */
    private $handle;

    /**
     * @var float
     */
    private $start_time;

    /**
     * @var array
     */
    private $event_listeners = [];

    /**
     * @var mixed
     */
    private $success_value = null;

    /**
     * @var mixed
     */
    private $error_value = null;

    /**
     * @param TaskInterface $task
     * @param mixed         $handle
     */
    public function __construct(TaskInterface $task, $handle)
    {
        $this->task       = $task;
        $this->handle     = $handle;
        $this->start_time = microtime(true);
    }

    /**
     * @return TaskInterface
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return mixed
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @return float
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * @param string   $ev
     * @param callable $cb
     */
    public function addEventListener($ev, $cb)
    {
        if (!isset($this->event_listeners[$ev])) {
            $this->event_listeners[$ev] = [];
        }

        $this->event_listeners[$ev][] = $cb;

        if ($ev === self::EVENT_DONE_SUCCESS && $this->success_value !== null) {
            call_user_func($cb, $this->success_value);
        } elseif ($ev === self::EVENT_DONE_FAILURE && $this->error_value !== null) {
            call_user_func($cb, $this->error_value);
        }
    }

    /**
     * Internal to the processor.
     *
     * @internal
     *
     * @param mixed $value
     */
    public function setSuccess($value = true)
    {
        if ($this->success_value !== null || $this->error_value !== null) {
            throw new \RuntimeException('Task result has already been set.');
        }

        $this->success_value = $value;

        $this->execEvent(self::EVENT_DONE_SUCCESS, $value);
    }

    /**
     * Internal to the processor.
     *
     * @internal
     *
     * @param mixed $value
     */
    public function setFailed($value = false)
    {
        if ($this->success_value !== null || $this->error_value !== null) {
            throw new \RuntimeException('Task result has already been set.');
        }

        $this->error_value = $value;

        $this->execEvent(self::EVENT_DONE_FAILURE, $value);
    }

    /**
     * @param string $ev
     * @param mixed  $value
     */
    private function execEvent($ev, $value)
    {
        if (!empty($this->event_listeners[$ev])) {
            foreach ($this->event_listeners[$ev] as $cb) {
                call_user_func($cb, $value, $this);
            }
        }
    }
}
