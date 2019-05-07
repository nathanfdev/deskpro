<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio\Model;

use JMS\Serializer\Annotation as JMS;
use Twilio\Rest\Pricing\V2\Voice\CountryInstance;

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
     * @param CountryInstance $apiCountry
     */
    public function __construct(CountryInstance $apiCountry)
    {
        $this->countryCode = $apiCountry->isoCountry;
        $this->countryName = $apiCountry->country;
    }
}
