<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Voice;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall as VoicePhoneCallEntity;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use JMS\Serializer\Annotation as JMS;

/**
 * Class VoicePhoneCall.
 */
class VoicePhoneCall
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $taskSid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $callSid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $conferenceSid;

    /**
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\VoiceNumber>")
     *
     * @var VoiceNumber
     */
    private $number;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $externalNumber;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $externalNumberType;

    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    private $person;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $type;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $status;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $data;

    /**
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant")
     *
     * @var AbstractVoicePhoneCallParticipant[]
     */
    private $participants;

    /**
     * @var VoicePhoneCallLog[]
     */
    private $phoneCallLogs;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateStarted;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateEnded;

    /**
     * @JMS\Type("Application\DeskPRO\Entity\Blob")
     *
     * @var Blob
     */
    private $recording;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $duration;

    /**
     * @JMS\Type("deferred<entity<Application\DeskPRO\Entity\Ticket>>")
     *
     * @var Ticket
     */
    private $ticket;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $recordingIsDownloading = false;

    /**
     * Constructor.
     *
     * @param VoicePhoneCallEntity $phoneCall
     */
    public function __construct(VoicePhoneCallEntity $phoneCall)
    {
        $this->id                 = $phoneCall->getId();
        $this->taskSid            = $phoneCall->getTaskSid();
        $this->callSid            = $phoneCall->getCallSid();
        $this->conferenceSid      = $phoneCall->getConferenceSid();
        $this->number             = $phoneCall->getNumber();
        $this->externalNumber     = $phoneCall->getExternalNumber();
        $this->externalNumberType = $phoneCall->getExternalNumberType();
        $this->person             = $phoneCall->getPerson();
        $this->type               = $phoneCall->getType();
        $this->status             = $phoneCall->getStatus();
        $this->data               = $phoneCall->getData();
        $this->participants       = $phoneCall->getParticipants();
        $this->phoneCallLogs      = $phoneCall->getPhoneCallLogs();
        $this->dateCreated        = $phoneCall->getDateCreated();
        $this->dateStarted        = $phoneCall->getDateStarted();
        $this->dateEnded          = $phoneCall->getDateEnded();
        $this->recording          = $phoneCall->getRecording();
        $this->duration           = $phoneCall->getDuration();

        if (is_array($this->data) && array_key_exists('RecordingUrl', $this->data)) {
            if (!$this->recording) {
                $this->recordingIsDownloading = true;
            }

            unset($this->data['RecordingUrl']);
        }
    }

    /**
     * @param CallbackDeferredProperty $ticket
     *
     * @return $this
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }
}
