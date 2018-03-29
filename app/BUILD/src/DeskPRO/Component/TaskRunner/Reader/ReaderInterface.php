<?php

namespace DeskPRO\Component\TaskRunner\Reader;

interface ReaderInterface
{
    /**
     * @return null|\DeskPRO\Component\TaskRunner\Task\TaskInterface
     */
    public function getNext();
}
