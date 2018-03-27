<?php

/**
 * DeskPRO.
 *
 * @category Twilio
 */

namespace deskpro_clickatell_sms\Ticket\Actions\ActionDef;

use Application\DeskPRO\Tickets\Actions\ActionDef\AbstractActionDef;

class SmsClickatellActionDef extends AbstractActionDef
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Send a Clickatell SMS message';
    }

    /**
     * {@inheritdoc}
     */
    public function getTriggerActionClass()
    {
        return 'deskpro_clickatell_sms\\Ticket\\Actions\\SmsClickatellAction';
    }

    /**
     * {@inheritdoc}
     */
    public function processActionBuilderOptions(array $options)
    {
        return $options;
    }
}
