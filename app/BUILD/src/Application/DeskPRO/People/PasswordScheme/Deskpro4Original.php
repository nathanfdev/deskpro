<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People\PasswordScheme;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PasswordSchemeInterface;

class Deskpro4Original implements PasswordSchemeInterface
{
    public function checkInput($plain_password, $hashed_password)
    {
        return false; // unsupported
    }

    public function hashPassword(Person $person, $plain_password)
    {
        return sha1($person->salt.$plain_password);
    }

    public function checkPassword(Person $person, $hashed_password, $plain_password)
    {
        return $hashed_password === $this->hashPassword($person, $plain_password);
    }
}
