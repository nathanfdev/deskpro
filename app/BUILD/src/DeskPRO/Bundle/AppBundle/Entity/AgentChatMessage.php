<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AgentChatMessage.
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChatMessageRepository")
 * @ORM\Table(name="agent_chat_message", uniqueConstraints={@ORM\UniqueConstraint(name="uuid_unique",columns={"uuid"})})
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 *
 * @ORM\EntityListeners({"DeskPRO\Bundle\AppBundle\EventListener\Doctrine\AgentChatMessageListener"})
 */
class AgentChatMessage implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const STATUS_NEW       = 0;
    const STATUS_DELIVERED = 1;
    const STATUS_READ      = 2;

    /**
     * The unique message ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * A UUID of this message.
     *
     * @ORM\Column(type="string", length=36)
     *
     * @Assert\NotNull()
     * @Assert\Uuid()
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $uuid;

    /**
     * The chat this message was sent.
     *
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AgentChat", inversedBy="messages")
     * @ORM\JoinColumn(name="agent_chat_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\AgentChat>")
     *
     * @var AgentChat
     */
    protected $chat;

    /**
     * Person that sent this message.
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $person;

    /**
     * Person`s name (will never change, even Person changed they name).
     *
     * @ORM\Column(type="string", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $person_name;

    /**
     * The message itself. Note: this is HTML.
     *
     * @ORM\Column(type="text", nullable=false)
     *
     * @Assert\NotNull()
     * @Assert\NotBlank()
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $message;

    /**
     * Additional data attached to message (not implemented).
     *
     * @ORM\Column(type="json_array", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $metadata = [];

    /**
     * The date message was originally sent.
     *
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Message status (0 - brand new, 1 - delivered, 2 - read).
     *
     * @ORM\Column(type="integer", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $status = 0;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return $this
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
     * @JMS\VirtualProperty()
     * @JMS\Type("integer")
     */
    public function getTimestamp()
    {
        return $this->date_created->getTimestamp();
    }

    /**
     * @return Person|null
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
        $this->setModelField('person_name', $person->getDisplayName());

        return $this;
    }

    /**
     * @return string
     */
    public function getPersonName()
    {
        return $this->person_name;
    }

    /**
     * @return AgentChat
     */
    public function getChat()
    {
        return $this->chat;
    }

    /**
     * @param AgentChat $chat
     *
     * @return $this
     */
    public function setChat(AgentChat $chat = null)
    {
        $this->setModelField('chat', $chat);

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->setModelField('message', $message);

        return $this;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     *
     * @return $this
     */
    public function setMetadata($metadata)
    {
        $this->setModelField('metadata', $metadata);

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setModelField('status', $status);

        return $this;
    }
}
