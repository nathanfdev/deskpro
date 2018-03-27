<?php

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
