<?php

namespace DeskPRO\Bundle\AppBundle\Twilio\Model;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TwilioAvailableNumber.
 */
class TwilioAvailableNumber extends AbstractTwilioNumber
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $region;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $rateCenter;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $type;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $price;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $priceUnit;

    /**
     * Constructor.
     *
     * @param object       $apiNumber
     * @param VoiceAccount $account
     * @param bool         $added
     * @param string       $type
     * @param string       $price
     * @param string       $priceUnit
     * @param string       $priceUnit
     */
    public function __construct($apiNumber, VoiceAccount $account, $added, $type, $price, $priceUnit)
    {
        parent::__construct($apiNumber, $account, $added);

        $this->countryCode = $apiNumber->isoCountry;
        $this->region      = $apiNumber->region;
        $this->rateCenter  = $apiNumber->rateCenter;
        $this->type        = $type;
        $this->price       = $price;
        $this->priceUnit   = $priceUnit;
    }
}
