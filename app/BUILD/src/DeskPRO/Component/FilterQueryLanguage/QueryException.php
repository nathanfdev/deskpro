<?php

namespace DeskPRO\Component\FilterQueryLanguage;

class QueryException extends \Exception
{
    /**
     * @var string
     */
    public $query;

    /**
     * @param string $dql
     *
     * @return QueryException
     */
    public static function queryError($query)
    {
        $e        = new self('Query error: '.$query);
        $e->query = $query;

        return $e;
    }

    /**
     * @param string          $message
     * @param \Exception|null $previous
     *
     * @return QueryException
     */
    public static function syntaxError($message, $previous = null)
    {
        $e = new self(self::appendQueryToMessage('[Syntax Error] '.$message, $previous), 0, $previous);
        if ($previous && $previous instanceof self) {
            $e->query = $previous->query;
        }

        return $e;
    }

    /**
     * @param string          $message
     * @param \Exception|null $previous
     *
     * @return QueryException
     */
    public static function semanticalError($message, $previous = null)
    {
        $e = new self(self::appendQueryToMessage('[Semantical Error] '.$message, $previous), 0, $previous);
        if ($previous && $previous instanceof self) {
            $e->query = $previous->query;
        }

        return $e;
    }

    /**
     * @param string $message
     * @param null   $previous
     *
     * @return string
     */
    private static function appendQueryToMessage($message, $previous = null)
    {
        if ($previous && $previous instanceof self) {
            $message .= ' -- in query: '.$previous->getQuery();
        }

        return $message;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }
}
