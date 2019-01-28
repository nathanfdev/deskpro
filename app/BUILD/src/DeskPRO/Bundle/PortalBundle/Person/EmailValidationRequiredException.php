<?php

namespace DeskPRO\Bundle\PortalBundle\Person;

use Application\DeskPRO\Entity\Person;

/**
 * Class EmailValidationRequiredException.
 */
class EmailValidationRequiredException extends \RuntimeException
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Person
     */
    private $person;

    /**
     * Constructor.
     *
     * @param string $email
     * @param string $name
     * @param Person $person
     */
    public function __construct($email, $name = null, Person $person = null)
    {
        parent::__construct('email validation is required to perform this action');

        $this->email  = $email;
        $this->name   = $name;
        $this->person = $person;
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

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }
}
