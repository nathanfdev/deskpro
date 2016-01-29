<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Logging;

use DpSys\LowError\SystemErrorHandler;
use Monolog\Logger;

/**
 * This is a version of Monolog Logger that we use in DeskPRO that simply won't throw an exception
 * during a log. If an exception occurs in the normal Monolog Logger, we log it the deskpro way and move on.
 */
class NoExceptionsLogger extends Logger
{
    public function addRecord($level, $message, array $context = array())
    {
        try {
            return parent::addRecord($level, $message, $context);
        } catch (\Exception $e) {
            // this is disabled for now because it clogs the normal error.log file with an exception trace
            // dozens of times per request. ignore log exceptions silently.
            //SystemErrorHandler::handleException($e, false);
        }
    }
}
