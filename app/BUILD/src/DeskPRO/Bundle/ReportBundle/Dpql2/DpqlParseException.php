<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2;

/**
 * Class DpqlParseException.
 */
class DpqlParseException extends DpqlException
{
    /**
     * @var int
     */
    protected $queryLine;

    /**
     * @var string
     */
    protected $queryToken;

    /**
     * Constructor.
     *
     * @param int             $queryLine
     * @param string          $queryToken
     * @param string          $message
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct($queryLine, $queryToken, $message = '', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->queryLine  = $queryLine;
        $this->queryToken = $queryToken;
    }

    /**
     * @return int
     */
    public function getQueryLine()
    {
        return $this->queryLine;
    }

    /**
     * @return string
     */
    public function getQueryToken()
    {
        return $this->queryToken;
    }
}
