<?php

/**
 * DeskPRO.
 *
 * @category Twilio
 */

namespace deskpro_twilio_sms\Ticket\Actions\ActionDef;

use Application\DeskPRO\Tickets\Actions\ActionDef\AbstractActionDef;

class SmsTwilioActionDef extends AbstractActionDef
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Send a Twilio SMS message';
    }

    /**
     * {@inheritdoc}
     */
    public function getTriggerActionClass()
    {
        return 'deskpro_twilio_sms\\Ticket\\Actions\\SmsTwilioAction';
    }

    /**
     * {@inheritdoc}
     */
    public function processActionBuilderOptions(array $options)
    {
        return $options;
    }
}
