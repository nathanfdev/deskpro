<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter\Model;

/**
 * Class Worker.
 */
class Worker extends AbstractModel
{
    const ACTIVITY_IDLE    = 'idle';
    const ACTIVITY_OFFLINE = 'offline';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $typeId;

    /**
     * @var string
     */
    protected $activity;

    /**
     * @var \DateTime
     */
    protected $dateLastActive;

    /**
     * @var array
     */
    protected $pendingTaskIds = [];

    /**
     * @var array
     */
    protected $activeTaskIds = [];

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * @param string $typeId
     *
     * @return $this
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * @return string
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param string $activity
     *
     * @return $this
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return $this->activity === self::ACTIVITY_IDLE;
    }

    /**
     * @return bool
     */
    public function isOffline()
    {
        return $this->activity === self::ACTIVITY_OFFLINE;
    }

    /**
     * @return \DateTime
     */
    public function getDateLastActive()
    {
        return $this->dateLastActive;
    }

    /**
     * @param \DateTime $dateLastActive
     *
     * @return $this
     */
    public function setDateLastActive(\DateTime $dateLastActive = null)
    {
        $this->dateLastActive = $dateLastActive;

        return $this;
    }

    /**
     * @param int[] $pendingTaskIds
     *
     * @return $this
     */
    public function setPendingTaskIds(array $pendingTaskIds)
    {
        $this->pendingTaskIds = $pendingTaskIds;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getPendingTaskIds()
    {
        return $this->pendingTaskIds;
    }

    /**
     * @param Task $task
     *
     * @return $this
     */
    public function addPendingTask(Task $task)
    {
        if (!isset($this->pendingTaskIds[$task->getChannel()])) {
            $this->pendingTaskIds[$task->getChannel()] = [];
        }
        if (!in_array($task->getId(), $this->pendingTaskIds)[$task->getChannel()]) {
            $this->pendingTaskIds[$task->getChannel()][] = $task->getId();
        }

        return $this;
    }

    /**
     * @param Task $task
     *
     * @return $this
     */
    public function removePendingTask(Task $task)
    {
        if (!isset($this->pendingTaskIds[$task->getChannel()])) {
            $this->pendingTaskIds[$task->getChannel()] = [];
        }
        if (($key = array_search($task->getId(), $this->pendingTaskIds[$task->getChannel()])) !== false) {
            unset($this->pendingTaskIds[$task->getChannel()][$key]);
        }

        return $this;
    }

    /**
     * @param string $channel
     *
     * @return int[]
     */
    public function getPendingTaskIdsForChannel($channel)
    {
        if (!isset($this->pendingTaskIds[$channel])) {
            $this->pendingTaskIds[$channel] = [];
        }

        return $this->pendingTaskIds[$channel];
    }

    /**
     * @param string $channel
     *
     * @return bool
     */
    public function hasPendingTasksForChannel($channel)
    {
        return count($this->getPendingTaskIdsForChannel($channel)) > 0;
    }

    /**
     * @param int[] $activeTaskIds
     *
     * @return $this
     */
    public function setActiveTaskIds(array $activeTaskIds)
    {
        $this->activeTaskIds = $activeTaskIds;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getActiveTaskIds()
    {
        return $this->activeTaskIds;
    }

    /**
     * @param Task $task
     *
     * @return $this
     */
    public function addActiveTask(Task $task)
    {
        if (!isset($this->activeTaskIds[$task->getChannel()])) {
            $this->activeTaskIds[$task->getChannel()] = [];
        }
        if (!in_array($task->getId(), $this->activeTaskIds)[$task->getChannel()]) {
            $this->activeTaskIds[$task->getChannel()][] = $task->getId();
        }

        return $this;
    }

    /**
     * @param Task $task
     *
     * @return $this
     */
    public function removeActiveTask(Task $task)
    {
        if (!isset($this->activeTaskIds[$task->getChannel()])) {
            $this->activeTaskIds[$task->getChannel()] = [];
        }
        if (($key = array_search($task->getId(), $this->activeTaskIds[$task->getChannel()])) !== false) {
            unset($this->activeTaskIds[$task->getChannel()][$key]);
        }

        return $this;
    }

    /**
     * @param string $channel
     *
     * @return int[]
     */
    public function getActiveTaskIdsForChannel($channel)
    {
        if (!isset($this->activeTaskIds[$channel])) {
            $this->activeTaskIds[$channel] = [];
        }

        return $this->activeTaskIds[$channel];
    }

    /**
     * @param string $channel
     *
     * @return bool
     */
    public function hasActiveTasksForChannel($channel)
    {
        return count($this->getActiveTaskIdsForChannel($channel)) > 0;
    }
}
