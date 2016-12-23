<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class VoicePhoneCallParticipant.
 *
 * @ORM\Entity()
 * @ORM\Table(name="voice_phone_call_participants")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=30)
 * @ORM\DiscriminatorMap({
 *   "user" = "VoicePhoneCallParticipantUser",
 *   "agent" = "VoicePhoneCallParticipantAgent"
 * })
 *
 * @JMS\ExclusionPolicy("all")
 */
abstract class AbstractVoicePhoneCallParticipant implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall", inversedBy="participants")
     * @ORM\JoinColumn(name="phone_call_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var VoicePhoneCall
     */
    private $phoneCall;

    /**
     * @ORM\Column(name="call_sid", type="string", length=50)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $callSid;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    private $person;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @ORM\Column(name="date_joined", type="datetime", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateJoined;

    /**
     * @ORM\Column(name="date_left", type="datetime", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateLeft;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dateCreated = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return VoicePhoneCall
     */
    public function getPhoneCall()
    {
        return $this->phoneCall;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @return $this
     */
    public function setPhoneCall(VoicePhoneCall $phoneCall = null)
    {
        $this->setModelField('phoneCall', $phoneCall);

        return $this;
    }

    /**
     * @return string
     */
    public function getCallSid()
    {
        return $this->callSid;
    }

    /**
     * @param string $callSid
     *
     * @return $this
     */
    public function setCallSid($callSid)
    {
        $this->setModelField('callSid', $callSid);

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
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);

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
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated = null)
    {
        $this->setModelField('dateCreated', $dateCreated);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateJoined()
    {
        return $this->dateJoined;
    }

    /**
     * @param \DateTime $dateJoined
     *
     * @return $this
     */
    public function setDateJoined(\DateTime $dateJoined = null)
    {
        $this->setModelField('dateJoined', $dateJoined);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateLeft()
    {
        return $this->dateLeft;
    }

    /**
     * @param \DateTime $dateLeft
     *
     *  @return $this
     */
    public function setDateLeft(\DateTime $dateLeft = null)
    {
        $this->setModelField('dateLeft', $dateLeft);

        return $this;
    }
}
