<?php

namespace DeskPRO\Bundle\AppBundle\Twilio\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class TwilioActivities.
 */
class TwilioActivities
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $offlineSid;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $idleSid;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $idleDisabledSid;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $busySid;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $reservedSid;

    /**
     * Constructor.
     *
     * @param string $offlineSid
     * @param string $idleSid
     * @param string $idleDisabledSid
     * @param string $busySId
     * @param string $reservedSid
     */
    public function __construct($offlineSid, $idleSid, $idleDisabledSid, $busySId, $reservedSid)
    {
        $this->offlineSid      = $offlineSid;
        $this->idleSid         = $idleSid;
        $this->idleDisabledSid = $idleDisabledSid;
        $this->busySid         = $busySId;
        $this->reservedSid     = $reservedSid;
    }

    /**
     * @return string
     */
    public function getOfflineSid()
    {
        return $this->offlineSid;
    }

    /**
     * @return string
     */
    public function getIdleSid()
    {
        return $this->idleSid;
    }

    /**
     * @return string
     */
    public function getIdleDisabledSid()
    {
        return $this->idleDisabledSid;
    }

    /**
     * @return string
     */
    public function getBusySid()
    {
        return $this->busySid;
    }

    /**
     * @return string
     */
    public function getReservedSid()
    {
        return $this->reservedSid;
    }
}
