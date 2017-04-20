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

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\TicketMessage;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="ticket_message_attributes", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="attr_name", columns={"ticket_message_id", "name"})
 * })
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn("type")
 * @ORM\DiscriminatorMap({
 *     "voice_phone_call" = "TicketMessageVoicePhoneCall",
 *     "value" = "TicketMessageAttribute"
 * })
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @JMS\ExclusionPolicy("All")
 */
class TicketMessageAttribute implements EntityInterface, NotifyPropertyChanged
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
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\TicketMessage", inversedBy="attributes")
     * @ORM\JoinColumn(name="ticket_message_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var TicketMessage
     */
    protected $message;

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
     * TicketMessageAttribute constructor.
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
     * @return TicketMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param TicketMessage $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->setModelField('message', $message);

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
