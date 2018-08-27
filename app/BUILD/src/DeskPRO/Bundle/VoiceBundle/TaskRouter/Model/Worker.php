<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter\Model;

/**
 * Class Worker.
 */
class Worker extends AbstractModel
{
    const ACTIVITY_IDLE     = 'idle';
    const ACTIVITY_RESERVED = 'reserved';
    const ACTIVITY_BUSY     = 'busy';
    const ACTIVITY_OFFLINE  = 'offline';

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
    public function isReserved()
    {
        return $this->activity === self::ACTIVITY_RESERVED;
    }

    /**
     * @return bool
     */
    public function isBusy()
    {
        return $this->activity === self::ACTIVITY_BUSY;
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
}
