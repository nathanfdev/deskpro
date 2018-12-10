<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio\Model;

use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\VoiceBundle\Model\AbstractVoiceNumber;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractTwilioNumber.
 */
abstract class AbstractTwilioNumber extends AbstractVoiceNumber
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
    protected $nickname;

    /**
     * Constructor.
     *
     * @param object             $apiNumber
     * @param TwilioVoiceAccount $account
     * @param bool               $added
     */
    public function __construct($apiNumber, TwilioVoiceAccount $account, $added)
    {
        parent::__construct($apiNumber->phoneNumber, $added);

        $this->account  = $account;
        $this->nickname = $apiNumber->friendlyName;

        if (isset($apiNumber->capabilities['voice']) && $apiNumber->capabilities['voice']) {
            $this->voiceEnabled = true;
        }
        if (isset($apiNumber->capabilities['sms']) && $apiNumber->capabilities['sms']) {
            $this->smsEnabled = true;
        }
    }
}
