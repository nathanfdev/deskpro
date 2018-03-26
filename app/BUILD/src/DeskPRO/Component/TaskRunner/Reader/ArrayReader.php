<?php

namespace DeskPRO\Component\TaskRunner\Reader;

use DeskPRO\Component\TaskRunner\Task\Task;

class ArrayReader implements ReaderInterface
{
    /**
     * @var array
     */
    private $task_data = [];

    /**
     * @param array $data
     */
    public function add(array $data)
    {
        $this->task_data[] = $data;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->task_data);
    }

    /**
     * @return Task
     */
    public function getNext()
    {
        $d = array_pop($this->task_data);
        if (!$d) {
            return;
        }

        return new Task($d);
    }
}
