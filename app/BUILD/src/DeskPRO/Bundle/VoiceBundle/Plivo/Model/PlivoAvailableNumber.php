<?php

namespace DeskPRO\Bundle\VoiceBundle\Plivo\Model;

use DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount;
use JMS\Serializer\Annotation as JMS;
use Plivo\Resources\PhoneNumber\PhoneNumber;

/**
 * Class PlivoAvailableNumber.
 */
class PlivoAvailableNumber extends AbstractPlivoNumber
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $type;

    /**
     * Constructor.
     *
     * @param PlivoVoiceAccount $account
     * @param PhoneNumber       $number
     * @param bool              $added
     */
    public function __construct(PlivoVoiceAccount $account, PhoneNumber $number, $added)
    {
        parent::__construct('+'.$number->number, $added);

        $this->account      = $account;
        $this->type         = strtolower($number->type);
        $this->smsEnabled   = $number->smsEnabled;
        $this->voiceEnabled = $number->voiceEnabled;
        $this->smsRate      = number_format($number->smsRate, 2, '.', ',');
        $this->voiceRate    = number_format($number->voiceRate, 2, '.', ',');
        $this->price        = number_format($number->monthlyRentalRate, 2, '.', ',');
        $this->priceUnit    = 'USD';
        $this->region       = $number->region;
    }
}
