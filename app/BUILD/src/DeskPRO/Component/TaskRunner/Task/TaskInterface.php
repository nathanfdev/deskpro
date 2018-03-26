<?php

namespace DeskPRO\Component\TaskRunner\Task;

interface TaskInterface
{
    /**
     * Returns a unique ID for the task.
     *
     * @return string
     */
    public function getId();
}
