<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter;

use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\TaskQueue;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;

/**
 * Class MemoryAdapter.
 */
class MemoryAdapter implements StorageAdapterInterface
{
    /**
     * @var int
     */
    private $taskUuid = 1;

    /**
     * @var Task[]
     */
    private $tasks = [];

    /**
     * @var Worker[]
     */
    private $workers = [];

    /**
     * {@inheritdoc}
     */
    public function getActiveTasks()
    {
        return array_filter($this->tasks, function (Task $task) {
            return $task->isPending();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getTask($id)
    {
        return isset($this->tasks[$id]) ? $this->tasks[$id] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function saveTask(Task $task)
    {
        $task->setId($this->taskUuid);
        $this->tasks[$task->getId()] = $task;

        ++$this->taskUuid;
    }

    /**
     * {@inheritdoc}
     */
    public function getOnlineWorkersByType($type)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkerByType($type, $typeId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkersByType($type, array $typeIds)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getWorker($id)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkers(array $ids)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function saveWorker(Worker $worker)
    {
        $this->workers[$worker->getType()] = $worker;
    }

    /**
     * {@inheritdoc}
     */
    public function removeWorker($type, $typeId)
    {
        if (isset($this->workers[$type][$typeId])) {
            unset($this->workers[$type][$typeId]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTaskQueue($type, $typeId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function saveTaskQueue(TaskQueue $taskQueue)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function removeTaskQueue($type, $typeId)
    {
    }
}
