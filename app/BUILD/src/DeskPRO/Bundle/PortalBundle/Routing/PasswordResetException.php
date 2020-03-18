<?php

namespace DeskPRO\Bundle\PortalBundle\Routing;

use Application\DeskPRO\Entity\Person;

/**
 * Class PasswordResetException
 */
class PasswordResetException extends \Exception
{
    /**
     * @var Person
     */
    private $person;

    /**
     * PasswordResetException constructor.
     *
     * @param Person     $person
     */
    public function __construct(Person $person)
    {
        $this->person = $person;

        parent::__construct('Password requires reset.');
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }
}
