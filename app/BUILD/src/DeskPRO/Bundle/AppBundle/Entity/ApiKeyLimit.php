<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\ApiKey;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ApiKeyLimit.
 *
 * @ORM\Entity()
 * @ORM\Table(name="api_key_limits")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 */
class ApiKeyLimit implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;
    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var ApiKey
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\ApiKey", inversedBy="api_logs")
     * @ORM\JoinColumn(name="api_key_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $api_key;

    /**
     * @var int
     * @ORM\Column(type="integer", name="hit_limit")
     */
    protected $hit_limit;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $current;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $start_time;

    /**
     * interval in seconds.
     *
     * @var int
     * @ORM\Column(type="integer", name="time_interval")
     */
    protected $time_interval;

    /**
     * @var string
     * @ORM\Column(type="string", name="limit_type")
     */
    protected $limit_type;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->setModelField('id', $id);

        return $this;
    }

    /**
     * @return ApiKey
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @param ApiKey $api_key
     *
     * @return $this
     */
    public function setApiKey($api_key)
    {
        $this->setModelField('api_key', $api_key);

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->hit_limit;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->setModelField('hit_limit', $limit);

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * @param int $current
     *
     * @return $this
     */
    public function setCurrent($current)
    {
        $this->setModelField('current', $current);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * @param \DateTime $start_time
     *
     * @return $this
     */
    public function setStartTime($start_time)
    {
        $this->setModelField('start_time', $start_time);

        return $this;
    }

    /**
     * @return int
     */
    public function getInterval()
    {
        return $this->time_interval;
    }

    /**
     * @param int $interval
     *
     * @return $this
     */
    public function setInterval($interval)
    {
        $this->setModelField('time_interval', $interval);

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->limit_type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->setModelField('limit_type', $type);

        return $this;
    }
}
