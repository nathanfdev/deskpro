<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Event.
 *
 * @todo process it same as ActionAlert with JMS
 * @ORM\Entity
 * @ORM\Table(name="notify_notifications", indexes={@ORM\Index(name="target_id", columns={"target_id"})})
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 *
 * @category Entities
 */
class Notification implements EntityInterface, NotifyPropertyChanged, NotificationEntityInterface
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     */
    protected $target_id;

    /**
     * @var string
     * @ORM\Column(type="string", length=80)
     * @Assert\NotNull()
     */
    protected $uuid;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     * @Assert\NotNull()
     */
    protected $date_created;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default" = 0}, nullable=false)
     * @Assert\NotNull()
     */
    protected $is_dismissed = false;

    /**
     * @var array
     * @ORM\Column(type="json_array", nullable=false)
     * @Assert\NotNull()
     */
    protected $data;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     * @Assert\NotNull()
     */
    protected $type;

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
     * @return Notification
     */
    public function setType($type)
    {
        $this->setModelField('type', $type);

        return $this;
    }

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
     * @return mixed
     */
    public function getTargetId()
    {
        return $this->target_id;
    }

    /**
     * @param mixed $target_id
     *
     * @return Notification
     */
    public function setTargetId($target_id)
    {
        $this->setModelField('target_id', $target_id);

        return $this;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     *
     * @return Notification
     */
    public function setUuid($uuid)
    {
        $this->setModelField('uuid', $uuid);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param \DateTime $date_created
     *
     * @return Notification
     */
    public function setDateCreated($date_created)
    {
        $this->setModelField('date_created', $date_created);

        return $this;
    }

    /**
     * @return bool
     */
    public function isIsDismissed()
    {
        return $this->is_dismissed;
    }

    /**
     * @param bool $is_dismissed
     *
     * @return Notification
     */
    public function setIsDismissed($is_dismissed)
    {
        $this->setModelField('is_dismissed', $is_dismissed);

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return Notification
     */
    public function setData($data)
    {
        $this->setModelField('data', $data);

        return $this;
    }
}
