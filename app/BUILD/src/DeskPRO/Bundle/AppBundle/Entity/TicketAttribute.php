<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Ticket;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="ticket_attributes", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="attr_name", columns={"ticket_id", "name"})
 * })
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @JMS\ExclusionPolicy("All")
 */
class TicketAttribute implements EntityInterface, NotifyPropertyChanged
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
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Ticket", inversedBy="attributes")
     * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var Ticket
     */
    protected $ticket;

    /**
     * @ORM\Column(name="name", type="string", length=250, nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=5000, nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var int
     */
    protected $value;

    /**
     * @ORM\Column(type="datetime", name="date_created", nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @Assert\NotNull()
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * TicketAttribute constructor.
     *
     * @param string $name Attribute name
     */
    public function __construct($name)
    {
        $this->setModelField('name', $name);
        $this->dateCreated = new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return [$this->id];
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function setTicket($ticket)
    {
        $this->setModelField('ticket', $ticket);

        return $this;
    }

    /**
     * @return int
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->setModelField('value', $value);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }
}
