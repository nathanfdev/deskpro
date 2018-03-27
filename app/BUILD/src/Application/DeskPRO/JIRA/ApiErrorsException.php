<?php

namespace Application\DeskPRO\JIRA;

class ApiErrorsException extends \Exception
{
    public $errors;

    public function __construct(array $errors, \Exception $previous = null)
    {
        $this->errors = $errors;
        parent::__construct('API Error'.implode(' ', $errors), 400);
    }
}
