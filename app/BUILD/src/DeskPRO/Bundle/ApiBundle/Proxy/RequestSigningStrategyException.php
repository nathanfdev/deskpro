<?php

namespace DeskPRO\Bundle\ApiBundle\Proxy;

class RequestSigningStrategyException extends HttpProxyException
{
    const CODE_UNEXPECTED_CREDENTIALS = 10;

    public static function createUnexpectedCredentials($message = null, \Exception $previous = null)
    {
        $actualMessage = empty($message) ?  'unexpected credential' : $message;
        return new RequestSigningStrategyException(
            $actualMessage,
            RequestSigningStrategyException::CODE_UNEXPECTED_CREDENTIALS,
            $previous
        );
    }
}
