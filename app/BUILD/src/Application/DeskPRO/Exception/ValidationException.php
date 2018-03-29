<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Exception;

class ValidationException extends \Exception
{
    /**
     * @var string
     */
    private $code_name;

    /**
     * @param string $code_name
     * @param string $message
     *
     * @return ValidationException
     */
    public static function create($code_name, $message = null, $code = 1)
    {
        return new self($message ?: $code_name, $code, null, $code_name);
    }

    /**
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     * @param $code_name
     */
    public function __construct($message, $code, $previous, $code_name)
    {
        parent::__construct($message, $code, $previous);
        $this->code_name = $code_name;
    }

    public function getCodeName()
    {
        return $this->code_name;
    }
}
