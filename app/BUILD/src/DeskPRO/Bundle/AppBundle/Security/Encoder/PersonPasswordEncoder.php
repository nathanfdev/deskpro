<?php

namespace DeskPRO\Bundle\AppBundle\Security\Encoder;

use Application\DeskPRO\Entity\Person;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * Class PersonPasswordEncoder.
 */
class PersonPasswordEncoder implements PasswordEncoderInterface
{
    /**
     * @var Person
     */
    private $person;

    /**
     * Constructor.
     *
     * @param Person $person
     */
    public function __construct(Person $person)
    {
        $this->person = $person;
    }

    /**
     * {@inheritdoc}
     */
    public function encodePassword($raw, $salt)
    {
        return $this->person->hashPassword($raw);
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return $this->person->checkPassword($raw);
    }
}
