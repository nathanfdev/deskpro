<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio\Model;

use JMS\Serializer\Annotation as JMS;
use Twilio\Rest\Api\V2010\Account\AvailablePhoneNumberCountryInstance;

/**
 * Class TwilioCountry.
 */
class TwilioCountry
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $countryCode;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $countryName;

    /**
     * Constructor.
     *
     * @param AvailablePhoneNumberCountryInstance $apiCountry
     */
    public function __construct(AvailablePhoneNumberCountryInstance $apiCountry)
    {
        $this->countryCode = $apiCountry->countryCode;
        $this->countryName = $apiCountry->country;
    }
}
