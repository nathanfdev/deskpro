<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter\Model;

/**
 * Class Task.
 */
class Task extends AbstractModel
{
    const STATUS_PENDING  = 'pending';
    const STATUS_TIMEOUT  = 'timeout';
    const STATUS_DONE     = 'done';
    const STATUS_CANCELED = 'canceled';
    const STATUS_ERROR    = 'error';

    /**
     * @var string
     */
    protected $channel;

    /**
     * @var string
     */
    protected $priority = 1;

    /**
     * @var int[]
     */
    protected $workersIds = [];

    /**
     * @var int
     */
    protected $acceptedWorkerId;

    /**
     * @var int
     */
    protected $timeout;

    /**
     * @var string
     */
    protected $status = self::STATUS_PENDING;

    /**
     * @var string
     */
    protected $statusReason;

    /**
     * @var array
     */
    protected $rejectedBy = [];

    /**
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dateCreated = new \DateTime();
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     *
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $priority
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getWorkerIds()
    {
        return $this->workersIds;
    }

    /**
     * @param int[] $workersIds
     *
     * @return $this
     */
    public function setWorkersIds(array $workersIds)
    {
        $this->workersIds = $workersIds;

        return $this;
    }

    /**
     * @param int $workerId
     *
     * @return $this
     */
    public function addWorkerId($workerId)
    {
        if (!in_array($workerId, $this->workersIds)) {
            $this->workersIds[] = $workerId;
        }

        return $this;
    }

    /**
     * @param int $workerId
     *
     * @return $this
     */
    public function removeWorkerId($workerId)
    {
        if (($key = array_search($workerId, $this->workersIds)) !== false) {
            unset($this->workersIds[$key]);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getAcceptedWorkerId()
    {
        return $this->acceptedWorkerId;
    }

    /**
     * @param string $acceptedWorkerId
     *
     * @return $this
     */
    public function setAcceptedWorkerId($acceptedWorkerId)
    {
        $this->acceptedWorkerId = $acceptedWorkerId;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function isTimeout()
    {
        return $this->status === self::STATUS_TIMEOUT;
    }

    /**
     * @return bool
     */
    public function isDone()
    {
        return $this->status === self::STATUS_DONE;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->status === self::STATUS_ERROR;
    }

    /**
     * @return string
     */
    public function getStatusReason()
    {
        return $this->statusReason;
    }

    /**
     * @param string $statusReason
     *
     * @return $this
     */
    public function setStatusReason($statusReason)
    {
        $this->statusReason = $statusReason;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getRejectedBy()
    {
        return $this->rejectedBy;
    }

    /**
     * @param int[] $rejectedBy
     *
     * @return $this
     */
    public function setRejectedBy(array $rejectedBy)
    {
        $this->rejectedBy = $rejectedBy;

        return $this;
    }

    /**
     * @param int $workerId
     *
     * @return $this
     */
    public function addRejectedBy($workerId)
    {
        if (!in_array($workerId, $this->rejectedBy)) {
            $this->rejectedBy[] = $workerId;
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param |DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }
}
