<?php

namespace DeskPRO\Bundle\VoiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="voice_workers")
 *
 * Class Worker.
 */
class Worker extends AbstractEntity
{
    /**
     * @ORM\Column(name="type", type="string")
     *
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(name="type_id", type="integer")
     *
     * @var string
     */
    protected $typeId;

    /**
     * @ORM\Column(name="activity", type="string")
     *
     * @var string
     */
    protected $activity;

    /**
     * @ORM\Column(name="date_last_active", type="datetime", nullable=true)
     *
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
        $this->setModelField('type', $type);

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
        $this->setModelField('typeId', $typeId);

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
        $this->setModelField('activity', $activity);

        return $this;
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
        $this->setModelField('lastActiveDate', $dateLastActive);

        return $this;
    }
}
