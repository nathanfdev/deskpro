<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppStorage;

class Exception extends \DomainException
{
    const CODE_STATE_NOT_FOUND = 15;

    const CODE_ACCESS_DENIED = 20;

    const CODE_ACCESS_RULE_NOT_FOUND = 25;

    /**
     * @param null|string $message
     * @param null|\Throwable  $previous
     * @return Exception
     */
    public static function createStateNotFoundException($message = null, $previous = null) {
        $exceptionMessage = is_null($message) ? 'state not found' : $message;
        if ($previous) {
            return new Exception($exceptionMessage, Exception::CODE_STATE_NOT_FOUND, $previous);
        }
        return new Exception($exceptionMessage, Exception::CODE_STATE_NOT_FOUND);
    }

    /**
     * @param null|string $message
     * @param null|\Throwable  $previous
     * @return Exception
     */
    public static function createAccessDeniedException($message = null, $previous = null) {
        $exceptionMessage = is_null($message) ? 'permission denied' : $message;
        if ($previous) {
            return new Exception($exceptionMessage, Exception::CODE_ACCESS_DENIED, $previous);
        }
        return new Exception($exceptionMessage, Exception::CODE_ACCESS_DENIED);
    }

    /**
     * @param null|string $message
     * @param null|\Throwable  $previous
     * @return Exception
     */
    public static function createAccessRuleNotFoundException($message = null, $previous = null) {
        $exceptionMessage = is_null($message) ? 'no matching rules' : $message;
        if ($previous) {
            return new Exception($exceptionMessage, Exception::CODE_ACCESS_RULE_NOT_FOUND, $previous);
        }
        return new Exception($exceptionMessage, Exception::CODE_ACCESS_RULE_NOT_FOUND);
    }
}
