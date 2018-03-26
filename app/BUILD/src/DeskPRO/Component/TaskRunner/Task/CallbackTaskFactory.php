<?php

namespace DeskPRO\Component\TaskRunner\Task;

class CallbackTaskFactory implements TaskFactoryInterface
{
    /**
     * @var callable
     */
    private $fn;

    /**
     * @param callable $fn
     */
    public function __construct($fn)
    {
        $this->fn = $fn;
    }

    /**
     * @param mixed $data
     *
     * @return TaskInterface
     */
    public function createTask($data)
    {
        return call_user_func($this->fn, $data);
    }
}
