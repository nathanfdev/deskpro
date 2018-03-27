<?php

namespace DeskPRO\Bundle\InstallBundle\Schema\Exception;

class SchemaInstallException extends \Exception
{
    /**
     * @var string
     */
    private $query;

    public function __construct($query, $message, $code, \Exception $previous = null)
    {
        parent::__construct($message, (int) $code, $previous);
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
