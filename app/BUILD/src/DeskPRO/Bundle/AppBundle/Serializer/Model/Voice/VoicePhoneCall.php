<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
