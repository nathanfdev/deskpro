<?php

namespace DeskPRO\Component\TaskRunner\Task;

interface TaskFactoryInterface
{
    /**
     * Creates a task given some input from a reader.
     *
     * @param mixed $data
     *
     * @return TaskInterface
     */
    public function createTask($data);
}
