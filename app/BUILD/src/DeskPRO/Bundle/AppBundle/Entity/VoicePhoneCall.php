<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoicePhoneCall.
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\VoicePhoneCallRepository")
 * @ORM\Table(name="voice_phone_calls", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="call_sid", columns={"call_sid"}),
 *   @ORM\UniqueConstraint(name="conference_sid", columns={"conference_sid"})
 * })
 *
 * @UniqueEntity("callSid")
 * @UniqueEntity("conferenceSid")
 */
class VoicePhoneCall implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const TYPE_INCOMING  = 'incoming';
    const TYPE_OUTGOING  = 'outgoing';
    const TYPE_FORWARDED = 'forwarded';

    const EXTERNAL_NUMBER_TYPE_PHONE = 'phone';
    const EXTERNAL_NUMBER_TYPE_SIP   = 'sip';

    const STATUS_OUTGOING      = 'outgoing';
    const STATUS_PENDING       = 'pending';
    const STATUS_WARM_ADD      = 'warm_add';
    const STATUS_WARM_TRANSFER = 'warm_transfer';
    const STATUS_COLD_TRANSFER = 'cold_transfer';
    const STATUS_ACTIVE        = 'active';
    const STATUS_ENDED         = 'ended';
    const STATUS_CANCELED      = 'canceled';
    const STATUS_VOICEMAIL     = 'voicemail';
    const STATUS_FAILED        = 'failed';

    const DIRECTION_INBOUND  = 'inbound';
    const DIRECTION_OUTBOUND = 'outbound';

    const ENQUEUED_AS_USER  = 'user';
    const ENQUEUED_AS_AGENT = 'agent';

    const RESTRICTED_NUMBER = '737 874-2833';
    const BLOCKED_NUMBER    = '256-2533';
    const UNKNOWN_NUMBER    = '865-6696';
    const ANONYMOUS_NUMBER  = '266696687';
    const EMPTY_NUMBER      = '';

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="task_sid", type="string", length=50, nullable=true)
     *
     * @var string
     */
    private $taskSid;

    /**
     * @ORM\Column(name="task_sids", type="json_array", nullable=true)
     *
     * @var string[]
     */
    private $taskSids = [];

    /**
     * @ORM\Column(name="call_sid", type="string", length=50, nullable=true)
     *
     * @var string
     */
    private $callSid;

    /**
     * @ORM\Column(name="conference_sid", type="string", length=50, nullable=true)
     *
     * @var string
     */
    private $conferenceSid;

    /**
     * @ORM\Column(name="call_sids", type="json_array")
     *
     * @var array[]
     */
    private $callSids = [];

    /**
     * @ORM\ManyToOne(targetEntity="AbstractVoiceAccount")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var AbstractVoiceAccount
     */
    private $account;

    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceNumber")
     * @ORM\JoinColumn(name="number_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Assert\NotNull()
     *
     * @var VoiceNumber
     */
    private $number;

    /**
     * Keep plain number in case if the number is disabled or removed.
     *
     * @ORM\Column(name="number_plain", type="string", length=50, nullable=false)
     *
     * @var string
     */
    private $numberPlain;

    /**
     * @ORM\Column(name="external_number", type="string", length=50)
     *
     * @Assert\NotBlank()
     * @AppAssert\CallNumber()
     *
     * @var string
     */
    private $externalNumber;

    /**
     * @ORM\Column(name="external_number_type", type="string", length=50, nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $externalNumberType;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     *
     * @var Person
     */
    private $person;

    /**
     * @ORM\Column(name="type", type="string", length=50)
     *
     * @var string
     */
    private $type;

    /**
     * @ORM\Column(name="status", type="string", length=50)
     *
     * @var string
     */
    private $status = self::STATUS_PENDING;

    /**
     * @ORM\Column(name="data", type="json_array")
     *
     * @var array
     */
    private $data = [];

    /**
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant", mappedBy="phoneCall", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var AbstractVoicePhoneCallParticipant[]|ArrayCollection
     */
    private $participants;

    /**
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog", mappedBy="phoneCall", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var VoicePhoneCallLog[]|ArrayCollection
     */
    private $phoneCallLogs;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @ORM\Column(name="date_started", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $dateStarted;

    /**
     * @ORM\Column(name="date_waiting", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $dateWaiting;

    /**
     * @ORM\Column(name="date_ended", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $dateEnded;

    /**
     * @ORM\JoinColumn(name="full_recording_id", referencedColumnName="id", nullable=true, onDelete="SET NULL",
     *     unique=true, columnDefinition=null)
     * @ORM\OneToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceRecording", cascade={"persist", "detach"},
     *     fetch="EAGER")
     *
     * @var VoiceRecording|null
     */
    private $fullRecording = null;

    /**
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceRecording", mappedBy="phoneCall", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var VoiceRecording[]|ArrayCollection
     */
    private $recordings;

    /**
     * @ORM\OneToOne(targetEntity="VoiceMissedAgentCall", mappedBy="phoneCall")
     *
     * @var VoiceMissedAgentCall
     */
    private $agentVoicemailRecord;

    /**
     * @ORM\Column(name="cost", type="string", nullable=true)
     *
     * @var float
     */
    private $cost;

    /**
     * @ORM\Column(name="cost_currency", type="string", nullable=true)
     *
     * @var string
     */
    private $costCurrency;

    /**
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall", mappedBy="phoneCall")
     *
     * @var ArrayCollection|TicketMessageVoicePhoneCall[]
     */
    private $ticketMessageAttributes;

    /**
     * @ORM\Column(name="enqueued_as", type="string")
     *
     * @var string
     */
    private $enqueuedAs;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->participants            = new ArrayCollection();
        $this->phoneCallLogs           = new ArrayCollection();
        $this->ticketMessageAttributes = new ArrayCollection();
        $this->recordings              = new ArrayCollection();
        $this->dateCreated             = new \DateTime();
        $this->dateWaiting             = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return AbstractVoiceAccount
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param AbstractVoiceAccount $account
     *
     * @return $this
     */
    public function setAccount(AbstractVoiceAccount $account = null)
    {
        $this->setModelField('account', $account);

        return $this;
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
    public function setNumber(VoiceNumber $number = null)
    {
        $this->setModelField('number', $number);
        if ($number) {
            $this->setNumberPlain($number->getNumber());
            $this->setAccount($number->getAccount());
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getNumberPlain()
    {
        return $this->numberPlain;
    }

    /**
     * @param string $numberPlain
     *
     * @return $this
     */
    public function setNumberPlain($numberPlain)
    {
        $this->setModelField('numberPlain', $numberPlain);

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
        $taskSids   = $this->taskSids;
        $taskSids[] = $taskSid;

        $this->setModelField('taskSid', $taskSid);
        $this->setModelField('taskSids', $taskSids);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getTaskSids()
    {
        return $this->taskSids;
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
    public function getExternalNumber()
    {
        return $this->externalNumber;
    }

    /**
     * @param string $number
     *
     * @return $this
     */
    public function setExternalNumber($number)
    {
        if (preg_match('/^sip:/', $number)) {
            $this->setModelField('externalNumberType', self::EXTERNAL_NUMBER_TYPE_SIP);
        } else {
            $this->setModelField('externalNumberType', self::EXTERNAL_NUMBER_TYPE_PHONE);
        }

        $this->setModelField('externalNumber', $number);

        return $this;
    }

    /**
     * @return bool
     */
    public function isStrangeNumber()
    {
        $strangeNumbers = [
            self::RESTRICTED_NUMBER,
            self::BLOCKED_NUMBER,
            self::UNKNOWN_NUMBER,
            self::ANONYMOUS_NUMBER,
            self::EMPTY_NUMBER,
        ];

        $formattedExternalNumber = preg_replace('/[^0-9]/', '', $this->externalNumber);
        foreach ($strangeNumbers as &$strangeNumber) {
            $strangeNumber = preg_replace('/[^0-9]/', '', $strangeNumber);
        }

        return in_array($formattedExternalNumber, $strangeNumbers);
    }

    /**
     * @return string
     */
    public function getExternalNumberType()
    {
        return $this->externalNumberType;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isOutgoingCall()
    {
        return $this->type === self::DIRECTION_OUTBOUND;
    }

    /**
     * @return bool
     */
    public function isIncomingCall()
    {
        return $this->type === self::DIRECTION_INBOUND;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->setModelField('type', $type);
        $this->setEnqueuedAs($this->isIncomingCall() ? self::ENQUEUED_AS_USER : self::ENQUEUED_AS_AGENT);

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
     * @return bool
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function isColdTransfer()
    {
        return $this->status === self::STATUS_COLD_TRANSFER;
    }

    /**
     * @return bool
     */
    public function isWarmTransfer()
    {
        return $this->status === self::STATUS_WARM_TRANSFER;
    }

    /**
     * @return bool
     */
    public function isWarmAdd()
    {
        return $this->status === self::STATUS_WARM_ADD;
    }

    /**
     * @return bool
     */
    public function isVoicemail()
    {
        return $this->status === self::STATUS_VOICEMAIL;
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return $this->status === self::STATUS_CANCELED;
    }

    /**
     * @return bool
     */
    public function isEnded()
    {
        return $this->status === self::STATUS_ENDED;
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
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
     * @return AbstractVoicePhoneCallParticipant[]|ArrayCollection
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * @return AbstractVoicePhoneCallParticipant[]|ArrayCollection
     */
    public function getActiveParticipants()
    {
        return $this->participants->filter(function (AbstractVoicePhoneCallParticipant $participant) {
            return !$participant->getDateLeft();
        });
    }

    /**
     * @return ArrayCollection|VoicePhoneCallParticipantUser[]
     */
    public function getUserParticipants()
    {
        return $this->participants->filter(function (AbstractVoicePhoneCallParticipant $participant) {
            return $participant instanceof VoicePhoneCallParticipantUser;
        });
    }

    /**
     * @return ArrayCollection|VoicePhoneCallParticipantAgent[]
     */
    public function getAgentParticipants()
    {
        return $this->participants->filter(function (AbstractVoicePhoneCallParticipant $participant) {
            return $participant instanceof VoicePhoneCallParticipantAgent;
        });
    }

    /**
     * @return ArrayCollection|VoicePhoneCallParticipantAgent[]
     */
    public function getActiveAgentParticipants()
    {
        return $this->getAgentParticipants()->filter(function (AbstractVoicePhoneCallParticipant $participant) {
            return !$participant->getDateLeft();
        });
    }

    /**
     * @return VoicePhoneCallParticipantUser|null
     */
    public function getUserParticipant()
    {
        return $this->getUserParticipants()->first();
    }

    /**
     * @return VoicePhoneCallParticipantAgent|null
     */
    public function getActiveAgentParticipant()
    {
        return $this->getActiveAgentParticipants()->first();
    }

    /**
     * @return bool
     */
    public function hasAgentParticipants()
    {
        return $this->getAgentParticipants()->count() > 0;
    }

    /**
     * @param AbstractVoicePhoneCallParticipant $participant
     *
     * @return $this
     */
    public function addParticipant(AbstractVoicePhoneCallParticipant $participant)
    {
        $this->participants->add($participant);
        $participant->setPhoneCall($this);

        return $this;
    }

    /**
     * @param AbstractVoicePhoneCallParticipant $participant
     *
     * @return $this
     */
    public function removeParticipant(AbstractVoicePhoneCallParticipant $participant)
    {
        $this->participants->removeElement($participant);
        $participant->setPhoneCall(null);

        return $this;
    }

    /**
     * @param string $callSid
     *
     * @return AbstractVoicePhoneCallParticipant|null
     */
    public function getParticipantByCallSid($callSid)
    {
        foreach ($this->participants as $participant) {
            if ($participant->getCallSid() === $callSid) {
                return $participant;
            }
        }

        return;
    }

    /**
     * @param Person $person
     *
     * @return AbstractVoicePhoneCallParticipant|null
     */
    public function getParticipantByPerson($person)
    {
        foreach ($this->participants as $participant) {
            if ($participant->getPerson() && $participant->getPerson() === $person) {
                return $participant;
            }
        }

        return;
    }

    /**
     * @param string $callSid
     *
     * @return Person|null
     */
    public function getPersonByCallSid($callSid)
    {
        $participant = $this->getParticipantByCallSid($callSid);

        return $participant ? $participant->getPerson() : null;
    }

    /**
     * @return bool
     */
    public function isOnHold()
    {
        $userParticipant = $this->getUserParticipants()->first();
        if ($userParticipant instanceof VoicePhoneCallParticipantUser) {
            return $userParticipant->isOnHold();
        }

        return false;
    }

    /**
     * @param VoicePhoneCallLog $phoneCallLog
     *
     * @return $this
     */
    public function addPhoneCallLog(VoicePhoneCallLog $phoneCallLog)
    {
        $this->phoneCallLogs->add($phoneCallLog);
        $phoneCallLog->setPhoneCall($this);

        return $this;
    }

    /**
     * @param VoicePhoneCallLog $phoneCallLog
     *
     * @return $this
     */
    public function removePhoneCallLog(VoicePhoneCallLog $phoneCallLog)
    {
        $this->phoneCallLogs->removeElement($phoneCallLog);
        $phoneCallLog->setPhoneCall(null);

        return $this;
    }

    /**
     * @return VoicePhoneCallLog[]|ArrayCollection
     */
    public function getPhoneCallLogs()
    {
        return $this->phoneCallLogs;
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
     * @return \DateTime
     */
    public function getDateStarted()
    {
        return $this->dateStarted;
    }

    /**
     * @param \DateTime $dateStarted
     *
     * @return $this
     */
    public function setDateStarted(\DateTime $dateStarted = null)
    {
        $this->setModelField('dateStarted', $dateStarted);
        $this->setDateWaiting(null);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateWaiting()
    {
        return $this->dateWaiting;
    }

    /**
     * @param \DateTime $dateWaiting
     *
     * @return $this
     */
    public function setDateWaiting(\DateTime $dateWaiting = null)
    {
        $this->setModelField('dateWaiting', $dateWaiting);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateEnded()
    {
        return $this->dateEnded;
    }

    /**
     * @param \DateTime $dateEnded
     *
     * @return $this
     */
    public function setDateEnded(\DateTime $dateEnded = null)
    {
        $this->setModelField('dateEnded', $dateEnded);
        $this->setDateWaiting(null);

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
     * @return int
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param int $cost
     *
     * @return $this
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * @param int $price
     *
     * @return $this
     */
    public function addCost($price)
    {
        if (function_exists('bcadd')) {
            $this->cost = bcadd($this->cost, $price, 8);
        } else {
            $this->cost += $price;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCostCurrency()
    {
        return $this->costCurrency;
    }

    /**
     * @param string $costCurrency
     *
     * @return $this
     */
    public function setCostCurrency($costCurrency)
    {
        $this->costCurrency = $costCurrency;

        return $this;
    }

    /**
     * @return VoiceRecording[]|ArrayCollection
     */
    public function getRecordings()
    {
        return $this->recordings;
    }

    /**
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function getTempRecordings()
    {
        return $this->recordings->filter(function (VoiceRecording $recording) {
            return $recording !== $this->fullRecording && !$recording->getMetadataProperty('full_recording');
        });
    }

    /**
     * @param VoiceRecording $recording
     *
     * @return $this
     */
    public function addRecording(VoiceRecording $recording)
    {
        $this->recordings->add($recording);
        $recording->setPhoneCall($this);

        return $this;
    }

    /**
     * @param VoiceRecording $recording
     *
     * @return $this
     */
    public function removeRecording(VoiceRecording $recording)
    {
        $this->recordings->removeElement($recording);
        $recording->setPhoneCall(null);

        return $this;
    }

    /**
     * @return VoiceMissedAgentCall
     */
    public function getAgentVoicemailRecord()
    {
        return $this->agentVoicemailRecord;
    }

    /**
     * @param VoiceMissedAgentCall $agentVoicemailRecord
     *
     * @return $this
     */
    public function setAgentVoicemailRecord(VoiceMissedAgentCall $agentVoicemailRecord = null)
    {
        $this->setModelField('voicemailRecord', $agentVoicemailRecord);
        if ($agentVoicemailRecord) {
            $agentVoicemailRecord->setPhoneCall($this);
        }

        return $this;
    }

    /**
     * @return array[]
     */
    public function getCallSids()
    {
        return $this->callSids;
    }

    /**
     * @return string[]
     */
    public function getFlattenCallSids()
    {
        $sids = [];
        foreach ($this->callSids as $callSid) {
            $sids[] = $callSid['callSid'];
        }

        return $sids;
    }

    /**
     * @param $participantId
     *
     * @return array
     */
    public function getParticipantCallSids($participantId)
    {
        $sids = [];
        foreach ($this->callSids as $callSid) {
            if ((int) $callSid['participant'] === (int) $participantId) {
                $sids[] = $callSid['callSid'];
            }
        }

        return $sids;
    }

    /**
     * @param int $participantId
     *
     * @return array
     */
    public function getParticipantForwardedCallSids($participantId)
    {
        $sids = [];
        foreach ($this->callSids as $callSid) {
            if ($callSid['participant'] === $participantId && $callSid['type'] === self::TYPE_FORWARDED) {
                $sids[] = $callSid['callSid'];
            }
        }

        return $sids;
    }

    /**
     * @param int    $participantId
     * @param string $type
     * @param string $callSid
     *
     * @return $this
     */
    public function addCallSid($participantId, $type, $callSid)
    {
        $callSids   = $this->callSids;
        $callSids[] = [
            'callSid'     => $callSid,
            'type'        => $type,
            'participant' => $participantId,
        ];

        $this->setModelField('callSids', $callSids);

        return $this;
    }

    /**
     * @return TicketMessageVoicePhoneCall[]|ArrayCollection
     */
    public function getTicketMessageAttributes()
    {
        return $this->ticketMessageAttributes;
    }

    /**
     * @return string
     */
    public function getEnqueuedAs()
    {
        return $this->enqueuedAs;
    }

    /**
     * @param string $enqueuedAs
     *
     * @return $this
     */
    public function setEnqueuedAs($enqueuedAs)
    {
        $this->setModelField('enqueuedAs', $enqueuedAs);

        return $this;
    }

    /**
     * @return bool
     */
    public function enqueuedAsUser()
    {
        return $this->enqueuedAs === self::ENQUEUED_AS_USER;
    }

    /**
     * @return bool
     */
    public function enqueuedAsAgent()
    {
        return $this->enqueuedAs === self::ENQUEUED_AS_AGENT;
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return 'queue'.$this->getId();
    }

    /**
     * @return string
     */
    public function getConferenceName()
    {
        return 'conference'.$this->getId();
    }

    /**
     * @return VoiceRecording|null
     */
    public function getFullRecording()
    {
        return $this->fullRecording;
    }

    /**
     * @param VoiceRecording $fullRecording
     *
     * @return $this
     */
    public function setFullRecording(VoiceRecording $fullRecording = null)
    {
        $this->setModelField('fullRecording', $fullRecording);
        if ($fullRecording) {
            $fullRecording->setPhoneCall($this);
            $fullRecording->setMetadataProperty('full_recording', 1);
        }

        return $this;
    }

    /**
     * @return \Application\DeskPRO\Entity\Ticket|null
     */
    public function getTicket()
    {
        foreach ($this->ticketMessageAttributes as $attr) {
            if ($attr->getName() === TicketMessageVoicePhoneCall::ATTR_NAME) {
                return $attr->getMessage()->getTicket();
            }
        }

        return;
    }
}
