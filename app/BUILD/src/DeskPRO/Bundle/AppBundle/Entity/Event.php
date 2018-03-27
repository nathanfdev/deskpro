<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Event.
 *
 * @ORM\Entity
 * @ORM\Table(name="notification_system_event")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 *
 * @category Entities
 */
class Event implements EntityInterface, NotifyPropertyChanged
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
     * @var SystemEventInterface
     * @ORM\Column(type="object")
     */
    protected $event;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $processed = false;

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
     * @return Event
     */
    public function setId($id)
    {
        $this->setModelField('id', $id);

        return $this;
    }

    /**
     * @return SystemEventInterface
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return Event
     */
    public function setEvent(SystemEventInterface $event)
    {
        $this->setModelField('event', $event);

        return $this;
    }

    /**
     * @return bool
     */
    public function isPorcessed()
    {
        return $this->processed;
    }

    /**
     * @param $processed
     *
     * @return $this
     */
    public function setIsPorcessed($processed)
    {
        $this->setModelField('processed', (bool) $processed);

        return $this;
    }
}
