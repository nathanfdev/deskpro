<?php

namespace DeskPRO\Component\FilterQueryLanguage;

class QueryException extends \Exception
{
    /**
     * @var string
     */
    private $query;

    /**
     * @param string $dql
     *
     * @return QueryException
     */
    public static function queryError($query)
    {
        return new self($query);
    }

    /**
     * @param string          $message
     * @param \Exception|null $previous
     *
     * @return QueryException
     */
    public static function syntaxError($message, $previous = null)
    {
        return new self('[Syntax Error] '.$message, 0, $previous);
    }

    /**
     * @param string          $message
     * @param \Exception|null $previous
     *
     * @return QueryException
     */
    public static function semanticalError($message, $previous = null)
    {
        return new self('[Semantical Error] '.$message, 0, $previous);
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }
}
