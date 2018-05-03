<?php

namespace DeskPRO\Bundle\AppBundle\Form\Error;

use DeskPRO\Bundle\AppBundle\Limits\Exception\LimitExhaustedException;

/**
 * Class ExceptionErrorCodeFactory.
 */
class ExceptionErrorCodeFactory
{
    public static $exceptions_to_error_codes_map = [
        'DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException' => ErrorsCodes::BAD_REQUEST,
        LimitExhaustedException::class                                       => ErrorsCodes::RATE_LIMITS,
        'Symfony\Component\HttpKernel\Exception\BadRequestHttpException'     => ErrorsCodes::BAD_REQUEST,
        'Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException'   => ErrorsCodes::UNAUTHORIZED,
        'Symfony\Component\HttpKernel\Exception\NotFoundHttpException'       => ErrorsCodes::NOT_FOUND,
        'Symfony\Component\Security\Core\Exception\BadCredentialsException'  => ErrorsCodes::BAD_CREDENTIALS,
    ];

    public static $exception_messages_to_error_codes_map = [
        'Invalid JSONP callback value'  => ErrorsCodes::INVALID_JSONP_CALLBACK,
        'Invalid json message received' => ErrorsCodes::INVALID_JSON_BODY,
    ];

    /**
     * @param \Exception $exception
     *
     * @return string
     */
    public function getExceptionErrorCode(\Exception $exception)
    {
        $error_code = $this->findMappedMessage($exception->getMessage());
        if ($error_code) {
            return $error_code;
        }

        $error_code = $exception->getMessage();
        if ($error_code) {
            return $error_code;
        }

        $error_code = $this->findMappedErrorCode(get_class($exception));
        if ($error_code) {
            return $error_code;
        }

        return ErrorsCodes::EXCEPTION_FALLBACK;
    }

    /**
     * @param $exception_class
     *
     * @return string
     */
    protected function findMappedErrorCode($exception_class)
    {
        foreach (self::$exceptions_to_error_codes_map as $exception_name => $error_code) {
            if ($exception_name === $exception_class) {
                return $error_code;
            }
        }

        return '';
    }

    /**
     * @param $exception_message
     *
     * @return string
     */
    protected function findMappedMessage($exception_message)
    {
        foreach (self::$exception_messages_to_error_codes_map as $mapped_message => $error_code) {
            if ($mapped_message === $exception_message) {
                return $error_code;
            }
        }

        return '';
    }
}
