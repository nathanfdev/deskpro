<?php

namespace DeskPRO\Component\TaskRunner\Task;

class JsonTaskFactory implements TaskFactoryInterface
{
    /**
     * @param mixed $data
     *
     * @return TaskInterface
     */
    public function createTask($data)
    {
        $d = json_decode($data, true);

        return new Task($d);
    }
}
