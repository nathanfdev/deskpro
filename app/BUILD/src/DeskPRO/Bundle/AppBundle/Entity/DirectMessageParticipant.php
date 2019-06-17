<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\DirectMessageParticipantRepository")
 * @ORM\Table(name="direct_message_participants", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="dm_person_thread_id", columns={"person_id", "thread_id"})
 * })
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class DirectMessageParticipant implements EntityInterface, NotifyPropertyChanged
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
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     *
     * @var Person
     */
    protected $person;

    /**
     * @ORM\ManyToOne(targetEntity="DirectMessageThread")
     * @ORM\JoinColumn(name="thread_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @var DirectMessageThread
     */
    protected $thread;

    /**
     * @ORM\Column(name="is_unread", type="boolean")
     *
     * @var bool
     */
    protected $isUnread = false;

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
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return DirectMessageThread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @param DirectMessageThread $thread
     *
     * @return $this
     */
    public function setThread(DirectMessageThread $thread)
    {
        $this->setModelField('thread', $thread);

        return $this;
    }

    /**
     * @return bool
     */
    public function isUnread()
    {
        return $this->isUnread;
    }

    /**
     * @param bool $isUnread
     *
     * @return $this
     */
    public function setIsUnread($isUnread)
    {
        $this->setModelField('isUnread', $isUnread);

        return $this;
    }
}
