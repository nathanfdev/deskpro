<?php

namespace DeskPRO\Bundle\AppStoreBundle\API;

use DeskPRO\Bundle\AppStoreBundle\Domain;
use Symfony\Component\HttpKernel\Exception;

class HttpExceptionConverter
{
    /**
     * @param Domain\AppStorage\Exception $e
     *
     * @return Exception\HttpException
     */
    public static function fromApplicationStateException(Domain\AppStorage\Exception $e)
    {
        switch ($e->getCode()) {
            case Domain\AppStorage\Exception::CODE_STATE_NOT_FOUND:
                return new Exception\NotFoundHttpException($e->getMessage(), $e);
            case Domain\AppStorage\Exception::CODE_ACCESS_RULE_NOT_FOUND:
                return new Exception\UnprocessableEntityHttpException($e->getMessage(), $e);
            case Domain\AppStorage\Exception::CODE_ACCESS_DENIED:
                return new Exception\UnprocessableEntityHttpException($e->getMessage(), $e);
            default:
                return new Exception\HttpException(500, 'unknown exception while processing application state', $e);
        }
    }
}
