<?php

namespace Application\DeskPRO\JIRA;

class ApiCoreException extends \Exception
{
    public $errors;

    public function __construct(array $errors, \Exception $previous = null)
    {
        $this->errors = $errors;
        parent::__construct('API Error. '.implode(' ', $errors), 400, $previous);
    }
}
