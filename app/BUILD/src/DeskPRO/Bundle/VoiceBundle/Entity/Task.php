<?php

namespace DeskPRO\Bundle\VoiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="voice_tasks")
 *
 * Class Task.
 */
class Task extends AbstractEntity
{
    /**
     * @ORM\Column(name="channel", type="string")
     *
     * @var string
     */
    protected $channel;

    /**
     * @ORM\Column(name="priority", type="integer")
     *
     * @var int
     */
    protected $priority;

    /**
     * @ORM\Column(name="workers", type="json_array", nullable=true)
     *
     * @var int[]
     */
    protected $workers;

    /**
     * @ORM\Column(name="accepted_worker", type="integer", nullable=true)
     *
     * @var int
     */
    protected $acceptedWorker;

    /**
     * @ORM\Column(name="timeout", type="integer", nullable=true)
     *
     * @var int
     */
    protected $timeout;

    /**
     * @ORM\Column(name="status", type="string")
     *
     * @var string
     */
    protected $status;

    /**
     * @ORM\Column(name="status_reason", type="string", nullable=true)
     *
     * @var string
     */
    protected $statusReason;

    /**
     * @ORM\Column(name="rejected_by", type="json_array", nullable=true)
     *
     * @var array
     */
    protected $rejectedBy = [];

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

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
        $this->setModelField('channel', $channel);

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->setModelField('priority', $priority);

        return $this;
    }

    /**
     * @return int[]
     */
    public function getWorkers()
    {
        return $this->workers;
    }

    /**
     * @param int[] $workers
     *
     * @return $this
     */
    public function setWorkers(array $workers)
    {
        $this->setModelField('workers', $workers);

        return $this;
    }

    /**
     * @return int
     */
    public function getAcceptedWorker()
    {
        return $this->acceptedWorker;
    }

    /**
     * @param int $acceptedWorker
     *
     * @return $this
     */
    public function setAcceptedWorker($acceptedWorker)
    {
        $this->setModelField('acceptedWorker', $acceptedWorker);

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
        $this->setModelField('timeout', $timeout);

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
        $this->setModelField('status', $status);

        return $this;
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
        $this->setModelField('statusReason', $statusReason);

        return $this;
    }

    /**
     * @return array
     */
    public function getRejectedBy()
    {
        return $this->rejectedBy;
    }

    /**
     * @param array $rejectedBy
     *
     * @return $this
     */
    public function setRejectedBy(array $rejectedBy)
    {
        $this->setModelField('rejectedBy', $rejectedBy);

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
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated = null)
    {
        $this->setModelField('dateCreated', $dateCreated);

        return $this;
    }
}
