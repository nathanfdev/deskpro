<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("all")
 */
class TicketMessageVoicePhoneCall extends TicketMessageAttribute
{
    const ATTR_NAME = 'voice_phone_call';

    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall")
     * @ORM\JoinColumn(name="phone_call_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall>")
     *
     * @var VoicePhoneCall
     */
    protected $phoneCall;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct(self::ATTR_NAME);
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
    public function setPhoneCall(VoicePhoneCall $phoneCall)
    {
        $this->setModelField('phoneCall', $phoneCall);

        return $this;
    }
}
