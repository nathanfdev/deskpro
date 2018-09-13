<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter;

use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\TaskQueue;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;

/**
 * Class RedisAdapter.
 */
class RedisAdapter implements StorageAdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getActiveTasks()
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getTask($id)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function saveTask(Task $task)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getOnlineWorkersByType($type)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkerByType($type, $typeId)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkersByType($type, array $typeIds)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getWorker($id)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkers(array $ids)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function saveWorker(Worker $worker)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function removeWorker($type, $typeId)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getTaskQueue($type, $typeId)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function saveTaskQueue(TaskQueue $taskQueue)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function removeTaskQueue($type, $typeId)
    {
        throw new \RuntimeException('Not implemented');
    }
}
