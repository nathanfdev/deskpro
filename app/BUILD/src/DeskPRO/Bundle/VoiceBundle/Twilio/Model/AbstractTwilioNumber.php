<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio\Model;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use JMS\Serializer\Annotation as JMS;
use libphonenumber\PhoneNumberUtil;

/**
 * Class AbstractTwilioNumber.
 */
abstract class AbstractTwilioNumber
{
    /**
     * @var string
     *
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\VoiceAccount>")
     */
    protected $account;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $numberCountryCode;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $nationalNumber;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $number;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $nickname;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $countryCode;

    /**
     * @var array
     *
     * @JMS\Type("array<string>")
     */
    protected $capabilities = [];

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $added;

    /**
     * Constructor.
     *
     * @param object       $apiNumber
     * @param VoiceAccount $account
     * @param bool         $added
     */
    public function __construct($apiNumber, VoiceAccount $account, $added)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $number    = $phoneUtil->parse($apiNumber->phoneNumber, null);

        $this->numberCountryCode = $number->getCountryCode();
        $this->nationalNumber    = $number->getNationalNumber();
        $this->number            = $apiNumber->phoneNumber;

        $this->account  = $account;
        $this->nickname = $apiNumber->friendlyName;
        $this->added    = $added;

        foreach ($apiNumber->capabilities as $capability => $enabled) {
            if ($enabled) {
                $this->capabilities[] = $capability;
            }
        }
    }
}
