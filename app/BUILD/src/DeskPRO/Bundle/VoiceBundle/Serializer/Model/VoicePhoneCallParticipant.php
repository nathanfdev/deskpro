<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Model;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant;
use JMS\Serializer\Annotation as JMS;

/**
 * Class VoicePhoneCallParticipant.
 */
class VoicePhoneCallParticipant
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
    private $callSid;

    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    private $person;

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
    private $dateJoined;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateLeft;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $cost;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $costCurrency;

    /**
     * Constructor.
     *
     * @param AbstractVoicePhoneCallParticipant $phoneCall
     */
    public function __construct(AbstractVoicePhoneCallParticipant $phoneCall)
    {
        $this->id           = $phoneCall->getId();
        $this->callSid      = $phoneCall->getCallSid();
        $this->person       = $phoneCall->getPerson();
        $this->dateCreated  = $phoneCall->getDateCreated();
        $this->dateJoined   = $phoneCall->getDateJoined();
        $this->dateLeft     = $phoneCall->getDateLeft();
        $this->cost         = number_format($phoneCall->getCost(), 3, '.', ',');
        $this->costCurrency = $phoneCall->getCostCurrency();
    }
}
