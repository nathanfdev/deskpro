<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2;

/**
 * The exception for an error that occurs when compiling.
 */
class DpqlCompileException extends DpqlException
{
    /**
     * @var string
     */
    private $query;

    /**
     * @param DpqlException $e
     * @param               $query
     *
     * @return DpqlCompileException
     */
    public static function createFromException(DpqlException $e, $query)
    {
        $message = $e->getMessage().' -- Query: '.$query;

        return new self($message, $e->getCode(), $e, $query);
    }

    public function __construct($message = '', $code = 0, $previous = null, $query = '')
    {
        parent::__construct($message, $code, $previous);
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }
}
