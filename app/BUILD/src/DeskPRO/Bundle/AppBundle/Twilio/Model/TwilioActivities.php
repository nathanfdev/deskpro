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
    private $busySId;

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
        $this->busySId         = $busySId;
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
    public function getBusySId()
    {
        return $this->busySId;
    }

    /**
     * @return string
     */
    public function getReservedSid()
    {
        return $this->reservedSid;
    }
}
