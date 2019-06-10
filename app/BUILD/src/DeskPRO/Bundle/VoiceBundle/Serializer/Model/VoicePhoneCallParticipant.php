<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Model;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantAgent;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantUser;
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
    private $type;

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
     * @param AbstractVoicePhoneCallParticipant $participant
     */
    public function __construct(AbstractVoicePhoneCallParticipant $participant)
    {
        $this->id           = $participant->getId();
        $this->callSid      = $participant->getCallSid();
        $this->person       = $participant->getPerson();
        $this->dateCreated  = $participant->getDateCreated();
        $this->dateJoined   = $participant->getDateJoined();
        $this->dateLeft     = $participant->getDateLeft();
        $this->cost         = $participant->getCost() ? number_format($participant->getCost(), 3, '.', ',') : null;
        $this->costCurrency = $participant->getCostCurrency();

        if ($participant instanceof VoicePhoneCallParticipantUser) {
            $this->type = 'user';
        } elseif ($participant instanceof VoicePhoneCallParticipantAgent) {
            $this->type = 'agent';
        }
    }
}
