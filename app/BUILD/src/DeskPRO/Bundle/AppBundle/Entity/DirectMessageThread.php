<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\DirectMessageThreadRepository")
 * @ORM\Table(name="direct_message_threads")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class DirectMessageThread implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    protected $id;

    /**
     * Array as a sorted comma-separated list with person id's, with a unique index on it
     * The purpose is mostly to verify db integrity with a unique constraint. we don't ever want two threads between the same people.
     *
     * Can't user simple_array field because we need an unique index here
     *
     * @ORM\Column(name="participant_ids", type="string", length=255, unique=true, nullable=true)
     *
     * @var array
     */
    protected $participantIds;

    /**
     * @ORM\Column(name="date_last_message", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $dateLastMessage;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setModelField('dateCreated', new \DateTime());
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
     * @return $this
     */
    public function setId($id)
    {
        $this->setModelField('id', $id);

        return $this;
    }

    /**
     * @return array
     */
    public function getParticipantIds()
    {
        return explode(',', $this->participantIds);
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function addParticipantId($id)
    {
        $id  = (int) $id;
        $ids = $this->participantIds ? explode(',', $this->participantIds) : [];
        if (!in_array($id, $ids)) {
            $ids[] = $id;
            sort($ids);
            $this->setModelField('participantIds', implode(',', $ids));
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateLastMessage()
    {
        return $this->dateLastMessage;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDateLastMessage(\DateTime $date)
    {
        $this->setModelField('dateLastMessage', $date);

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
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDateCreated($date)
    {
        $this->setModelField('dateCreated', $date);

        return $this;
    }
}
