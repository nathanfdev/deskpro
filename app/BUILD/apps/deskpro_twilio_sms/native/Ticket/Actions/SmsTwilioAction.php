<?php

/**
 * DeskPRO.
 *
 * @category Twilio
 */

namespace deskpro_twilio_sms\Ticket\Actions;

use Application\DeskPRO\Tickets\Actions\AbstractSmsAction;
use Orb\Sms\Provider\TwilioSmsProvider;

class SmsTwilioAction extends AbstractSmsAction
{
    /**
     * @var TwilioSmsProvider
     */
    private $twilio_provider;

    /**
     * {@inheritdoc}
     */
    public function getSmsProvider()
    {
        if ($this->twilio_provider) {
            return $this->twilio_provider;
        }

        $sid   = $this->getApp()->getSetting('account_sid');
        $token = $this->getApp()->getSetting('auth_token');

        $this->twilio_provider = new TwilioSmsProvider($sid, $token);

        return $this->twilio_provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromPhoneNumber()
    {
        return $this->getApp()->getSetting('from_number');
    }
}
