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

use Application\DeskPRO\Entity\Ticket;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoicePhoneCall.
 *
 * @ORM\Entity()
 * @ORM\Table(name="voice_phone_calls", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="call_sid", columns={"call_sid"}),
 *   @ORM\UniqueConstraint(name="conference_sid", columns={"conference_sid"})
 * })
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @UniqueEntity("sid")
 * @UniqueEntity("conferenceSid")
 */
class VoicePhoneCall implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const STATUS_PENDING       = 'pending';
    const STATUS_COLD_TRANSFER = 'cold_transfer';
    const STATUS_ACTIVE        = 'active';
    const STATUS_ENDED         = 'ended';

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
     * @ORM\Column(name="task_sid", type="string", length=50, nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $taskSid;

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
     * @ORM\Column(name="conference_sid", type="string", length=50, nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $conferenceSid;

    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceNumber")
     * @ORM\JoinColumn(name="number_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\VoiceNumber>")
     *
     * @Assert\NotNull()
     *
     * @var VoiceNumber
     */
    private $number;

    /**
     * @ORM\Column(name="from_number", type="string", length=50)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $fromNumber;

    /**
     * @ORM\Column(name="status", type="string", length=50)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $status = self::STATUS_PENDING;

    /**
     * @ORM\Column(name="data", type="json_array")
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    private $data;

    /**
     * @ORM\ManyToMany(targetEntity="Application\DeskPRO\Entity\Ticket", mappedBy="voicePhoneCalls", fetch="EXTRA_LAZY")
     *
     * @var Ticket[]|ArrayCollection
     */
    private $tickets;

    /**
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipant", mappedBy="phoneCall", cascade={"persist"}, orphanRemoval=true)
     *
     * @var VoicePhoneCallParticipant[]|ArrayCollection
     */
    private $agentParticipants;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->tickets           = new ArrayCollection();
        $this->agentParticipants = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return VoiceNumber
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param VoiceNumber $number
     *
     * @return $this
     */
    public function setNumber($number)
    {
        $this->setModelField('number', $number);

        return $this;
    }

    /**
     * @return string
     */
    public function getTaskSid()
    {
        return $this->taskSid;
    }

    /**
     * @param string $taskSid
     *
     * @return $this
     */
    public function setTaskSid($taskSid)
    {
        $this->setModelField('taskSid', $taskSid);

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
     * @return string
     */
    public function getConferenceSid()
    {
        return $this->conferenceSid;
    }

    /**
     * @param string $conferenceSid
     *
     * @return $this
     */
    public function setConferenceSid($conferenceSid)
    {
        $this->setModelField('conferenceSid', $conferenceSid);

        return $this;
    }

    /**
     * @return string
     */
    public function getFromNumber()
    {
        return $this->fromNumber;
    }

    /**
     * @param string $from
     *
     * @return $this
     */
    public function setFromNumber($from)
    {
        $this->setModelField('fromNumber', $from);

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setModelField('status', $status);

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
     * @return $this
     */
    public function setData(array $data = null)
    {
        $this->setModelField('data', $data);

        return $this;
    }

    /**
     * @return \Application\DeskPRO\Entity\Ticket[]|ArrayCollection
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function addTicket(Ticket $ticket)
    {
        $this->tickets->add($ticket);

        return $this;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function removeTicket(Ticket $ticket)
    {
        $this->tickets->removeElement($ticket);

        return $this;
    }

    /**
     * @return VoicePhoneCallParticipant[]|ArrayCollection
     */
    public function getAgentParticipants()
    {
        return $this->agentParticipants;
    }

    /**
     * @param VoicePhoneCallParticipant $participant
     *
     * @return $this
     */
    public function addAgentParticipant(VoicePhoneCallParticipant $participant)
    {
        $this->agentParticipants->add($participant);
        $participant->setPhoneCall($this);

        return $this;
    }

    /**
     * @param VoicePhoneCallParticipant $participant
     *
     * @return $this
     */
    public function removeAgentParticipant(VoicePhoneCallParticipant $participant)
    {
        $this->agentParticipants->removeElement($participant);
        $participant->setPhoneCall(null);

        return $this;
    }

    /**
     * @param string $callSid
     *
     * @return \Application\DeskPRO\Entity\Person|null
     */
    public function getPersonByCallSid($callSid)
    {
        $person = null;
        foreach ($this->agentParticipants as $participant) {
            if ($participant->getCallSid() === $callSid) {
                $person = $participant->getPerson();
            }
        }

        return $person;
    }
}
