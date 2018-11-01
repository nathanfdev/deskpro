<?php

namespace DeskPRO\Bundle\MessengerBundle\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class MessengerApiException extends BadRequestHttpException
{
    /**
     * @var array
     */
    private $errors;

    /**
     * MessengerApiException constructor.
     *
     * @param array          $errors
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(array $errors, $message = '', $code = 0, $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $previous, $code);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
