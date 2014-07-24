<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * Orb
 *
 * @package    Orb
 * @subpackage Sms
 */

namespace Orb\Sms;

class SmsInteractor
{
    /**
     * @var SmsProviderInterface
     */
    private $provider;

    public function __construct(SmsProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * The phone number should be in E.164 format, but the provider is ultimately in charge of handling formats.
     *
     * @param string $fromPhoneNumber the phone number to send from.
     * @param string $toPhoneNumber the phone number to send to.
     * @param string $textMessage   the message to be sent to the given number
     *
     * @throws Exception\SmsProviderException on failure
     * @return bool true on success, false on failure
     */
    public function sendMessage($fromPhoneNumber, $toPhoneNumber, $textMessage)
    {
        $this->provider->sendMessage($fromPhoneNumber, $toPhoneNumber, $textMessage);

        return true;
    }

    /**
     * Get the SmsProviderInterface instance that this SmsInteractor is using.
     *
     * @return SmsProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
