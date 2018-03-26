<?php

namespace deskpro_clickatell_sms\Ticket\Actions;

use Application\DeskPRO\Tickets\Actions\AbstractSmsAction;
use Orb\Sms\Provider\ClickatellSmsProvider;

/**
 * Class SmsClickatellAction.
 */
class SmsClickatellAction extends AbstractSmsAction
{
    /**
     * @var ClickatellSmsProvider
     */
    private $clickatell_provider;

    /**
     * {@inheritdoc}
     */
    public function getSmsProvider()
    {
        if (!$this->clickatell_provider) {
            $this->clickatell_provider = new ClickatellSmsProvider($this->getApp()->getSetting('auth_token'));
        }

        return $this->clickatell_provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromPhoneNumber()
    {
        return;
    }
}
