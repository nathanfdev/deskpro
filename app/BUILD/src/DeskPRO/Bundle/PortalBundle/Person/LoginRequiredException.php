<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Person;

use Application\DeskPRO\Entity\Person;

class LoginRequiredException extends \RuntimeException
{
    /**
     * @var Person
     */
    private $person;

    /**
     * @var string
     */
    private $email;

    /**
     * LoginRequiredException constructor.
     *
     * @param string $email
     * @param Person $person
     */
    public function __construct($email, Person $person)
    {
        $this->person = $person;
        $this->email  = $email;
        parent::__construct('login is required to perform this action');
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
}
