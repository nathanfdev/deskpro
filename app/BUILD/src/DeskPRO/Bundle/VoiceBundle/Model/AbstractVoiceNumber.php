<?php

namespace DeskPRO\Bundle\VoiceBundle\Model;

use JMS\Serializer\Annotation as JMS;
use libphonenumber\PhoneNumberUtil;

/**
 * Class AbstractNumber.
 */
abstract class AbstractVoiceNumber
{
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
    protected $countryCode;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $smsEnabled;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $voiceEnabled;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $added;

    /**
     * Constructor.
     *
     * @param string $number
     * @param bool   $added
     */
    public function __construct($number, $added)
    {
        $parsedNumber = PhoneNumberUtil::getInstance()->parse($number, null);

        $this->numberCountryCode = $parsedNumber->getCountryCode();
        $this->nationalNumber    = $parsedNumber->getNationalNumber();
        $this->number            = $number;
        $this->added             = $added;
    }
}
