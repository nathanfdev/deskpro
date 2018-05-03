<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Person;

class EmailValidationRequiredException extends \RuntimeException
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var null
     */
    private $name;

    public function __construct($email, $name = null)
    {
        parent::__construct('email validation is required to perform this action');
        $this->email = $email;
        $this->name  = $name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
