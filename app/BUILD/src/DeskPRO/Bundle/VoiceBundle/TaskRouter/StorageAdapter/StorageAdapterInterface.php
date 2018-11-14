<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter;

use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\TaskQueue;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;

/**
 * Interface AdapterInterface.
 */
interface StorageAdapterInterface
{
    /**
     * @param string $channel
     *
     * @return Task[]
     */
    public function getActiveTasks($channel = null);

    /**
     * @param int $id
     *
     * @return Task
     */
    public function getTask($id);

    /**
     * @param Task $task
     */
    public function saveTask(Task $task);

    /**
     * @param string $type
     *
     * @return Worker[]
     */
    public function getOnlineWorkersByType($type);

    /**
     * @param string $type
     * @param int    $typeId
     *
     * @return Worker
     */
    public function getWorkerByType($type, $typeId);

    /**
     * @param string $type
     * @param array  $typeIds
     *
     * @return Worker[]
     */
    public function getWorkersByType($type, array $typeIds);

    /**
     * @param int $id
     *
     * @return Worker|null
     */
    public function getWorker($id);

    /**
     * @param array $ids
     *
     * @return Worker[]
     */
    public function getWorkers(array $ids);

    /**
     * @param Worker $worker
     */
    public function saveWorker(Worker $worker);

    /**
     * @param string $type
     * @param int    $typeId
     */
    public function removeWorker($type, $typeId);

    /**
     * @param string $type
     * @param int    $typeId
     *
     * @return TaskQueue
     */
    public function getTaskQueue($type, $typeId);

    /**
     * @param TaskQueue $taskQueue
     */
    public function saveTaskQueue(TaskQueue $taskQueue);

    /**
     * @param string $type
     * @param int    $typeId
     */
    public function removeTaskQueue($type, $typeId);
}
