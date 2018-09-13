<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio\Model;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use JMS\Serializer\Annotation as JMS;
use libphonenumber\PhoneNumberUtil;

/**
 * Class TwilioExistingNumber.
 */
class TwilioExistingNumber extends AbstractTwilioNumber
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $sid;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    protected $dateCreated;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    protected $dateUpdated;

    /**
     * Constructor.
     *
     * @param object       $apiNumber
     * @param VoiceAccount $account
     * @param bool         $added
     */
    public function __construct($apiNumber, VoiceAccount $account, $added)
    {
        parent::__construct($apiNumber, $account, $added);

        // twilio doesn't provide region info for existing numbers, get it from the number
        $phoneUtil = PhoneNumberUtil::getInstance();
        $number    = $phoneUtil->parse($apiNumber->phoneNumber, null);

        $this->sid         = $apiNumber->sid;
        $this->dateCreated = $apiNumber->dateCreated;
        $this->dateUpdated = $apiNumber->dateUpdated;
        $this->countryCode = $phoneUtil->getRegionCodeForNumber($number);
    }
}
