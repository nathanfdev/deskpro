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

/**
 * Interface SmsProviderInterface
 *
 * An interface that defines an SMS Provider in the system. All SMS Providers that are used in this package
 * must implement this interface.
 *
 * @package Orb\Sms
 */
interface SmsProviderInterface
{
    /**
     * @param string $fromPhoneNumber phone number to send to, provider should be able to handle any format
     * @param string $toPhoneNumber phone number, provider should be able to handle any format
     * @param string $textMessage the message to be sent to the given number
     *
     * @throws \Orb\Sms\SmsException
     * @return \Orb\Sms\SmsResult
     */
    public function sendMessage($toPhoneNumber, $textMessage, $fromPhoneNumber);

    /**
     * A string identifier of the provider. This should be unique across the system.
     *
     * @return string
     */
    public function getName();
}
