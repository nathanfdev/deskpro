<?php

namespace DeskPRO\Bundle\VoiceBundle\Plivo\Model;

use DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount;
use JMS\Serializer\Annotation as JMS;
use Plivo\Resources\Number\Number;

/**
 * Class PlivoExistingNumber.
 */
class PlivoExistingNumber extends AbstractPlivoNumber
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $sid;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $carrier;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    protected $dateCreated;

    /**
     * Constructor.
     *
     * @param PlivoVoiceAccount $account
     * @param Number            $number
     * @param bool              $added
     */
    public function __construct(PlivoVoiceAccount $account, Number $number, $added)
    {
        parent::__construct('+'.$number->number, $added);

        $this->account      = $account;
        $this->smsEnabled   = $number->smsEnabled;
        $this->voiceEnabled = $number->voiceEnabled;
        $this->smsRate      = number_format($number->smsRate, 2, '.', ',');
        $this->voiceRate    = number_format($number->voiceRate, 2, '.', ',');
        $this->price        = number_format($number->monthlyRentalRate, 2, '.', ',');
        $this->priceUnit    = 'USD';
        $this->carrier      = $number->carrier;
        $this->region       = $number->region;
        $this->dateCreated  = new \DateTime($number->addedOn);
    }
}
