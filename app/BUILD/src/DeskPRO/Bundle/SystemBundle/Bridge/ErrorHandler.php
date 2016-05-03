<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\SystemBundle\Bridge;

use Psr\Log\LoggerInterface;

/**
 * Class ErrorHandler.
 *
 * This ErrorHandler combines Monolog and Bugsnag error handlers
 */
class ErrorHandler extends \Monolog\ErrorHandler
{
    /**
     * @var \Bugsnag_Client
     */
    private static $bugsnag;

    /**
     * Overriding to add bugsnag.
     *
     * {@inheritdoc}
     */
    public static function register(
        $bugsnagApiKey,
        LoggerInterface $logger,
        $errorLevelMap = [],
        $exceptionLevel = null,
        $fatalLevel = null
    ) {
        if ($bugsnagApiKey && !self::$bugsnag) {
            self::$bugsnag = new \Bugsnag_Client($bugsnagApiKey);
            set_error_handler([self::$bugsnag, 'errorHandler']);
        }

        return parent::register($logger, $errorLevelMap, $exceptionLevel, $fatalLevel);
    }

    /**
     * Log exception with bugsnag.
     *
     * @param \Exception $e
     */
    public static function bugsnagException($e)
    {
        self::$bugsnag->exceptionHandler($e);
    }

    /**
     * {@inheritdoc}
     */
    public function handleFatalError()
    {
        if (self::$bugsnag) {
            self::$bugsnag->shutdownHandler();
        }
        parent::handleFatalError();
    }
}
