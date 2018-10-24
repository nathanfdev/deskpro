<?php

namespace DeskPRO\Bundle\MessengerBundle\Exception;

use Throwable;

class MapperException extends \InvalidArgumentException
{
    /**
     * @var array
     */
    private $errors;

    /**
     * MapperException constructor.
     *
     * @param array          $errors
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(array $errors, $message = '', $code = 0, Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
