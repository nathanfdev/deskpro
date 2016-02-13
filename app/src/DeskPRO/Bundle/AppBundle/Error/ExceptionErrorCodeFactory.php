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
namespace DeskPRO\Bundle\AppBundle\Error;

/**
 * Class ExceptionErrorCodeFactory.
 */
class ExceptionErrorCodeFactory
{
    public static $exceptions_to_error_codes_map = [
        'DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException'     => ApiErrors::BAD_REQUEST,
        'Symfony\Component\HttpKernel\Exception\BadRequestHttpException'    => ApiErrors::BAD_REQUEST,
        'Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException'  => ApiErrors::UNAUTHORIZED,
        'Symfony\Component\HttpKernel\Exception\NotFoundHttpException'      => ApiErrors::NOT_FOUND,
        'Symfony\Component\Security\Core\Exception\BadCredentialsException' => ApiErrors::BAD_CREDENTIALS,
    ];

    public static $exception_messages_to_error_codes_map = [
        'Invalid JSONP callback value'  => ApiErrors::INVALID_JSONP_CALLBACK,
        'Invalid json message received' => ApiErrors::INVALID_JSON_BODY,
    ];

    /**
     * @param \Exception $exception
     *
     * @return string|void
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

        return ApiErrors::EXCEPTION_FALLBACK;
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
