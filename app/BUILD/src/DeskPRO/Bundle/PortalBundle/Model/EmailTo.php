<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Model;

use Application\DeskPRO\Entity\Person;

/**
 * Encapsulates logic around who to send an email to. Usually a person (just construct this with a Person) but
 * sometimes we send an email when we don't have a Person object yet, hence the need for this model.
 */
class EmailTo
{
    /**
     * @var Person|null
     */
    protected $person;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $name;

    /**
     * If you provide a Person you won't need to do anything else.
     *
     * If you omit Person from the constructer, you must call ::setTo(email, name)
     *
     * @param Person|null $person
     */
    public function __construct(Person $person = null)
    {
        $this->person = $person;
    }

    /**
     * @param $email_address
     * @param string|bool $name - false means don't change name
     */
    public function setTo($email_address, $name = false)
    {
        $this->email = $email_address;
        if ($name !== false) {
            $this->name = $name;
        }
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function getName()
    {
        return $this->name ?: ($this->person ? $this->person->getNameWithTitle() : '');
    }

    public function getEmailAddress()
    {
        return $this->email ?: ($this->person ? $this->person->getPrimaryEmailAddress() : '');
    }
}
