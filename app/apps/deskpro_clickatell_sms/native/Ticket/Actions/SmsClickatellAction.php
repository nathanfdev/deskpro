<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Twilio
 */

namespace deskpro_clickatell_sms\Ticket\Actions;

use Application\DeskPRO\Tickets\Actions\AbstractSmsAction;
use Orb\Sms\Provider\ClickatellSmsProvider;

class SmsClickatellAction extends AbstractSmsAction
{
    /**
     * @var ClickatellSmsProvider
     */
    private $clickatell_provider;

    /**
     * {@inheritDoc}
     */
    public function getSmsProvider()
    {
        if ($this->clickatell_provider) {
            return $this->clickatell_provider;
        }

        $username = $this->getApp()->getSetting('username');
        $password = $this->getApp()->getSetting('password');
        $api_id = $this->getApp()->getSetting('api_id');

        $this->clickatell_provider = new ClickatellSmsProvider($username, $password, $api_id);

        return $this->clickatell_provider;
    }

    /**
     * {@inheritDoc}
     */
    public function getFromPhoneNumber()
    {
        return null;
    }
}
