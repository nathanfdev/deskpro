<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People\PasswordScheme;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PasswordSchemeInterface;

class Deskpro3 implements PasswordSchemeInterface
{
    public function checkInput($plain_password, $hashed_password)
    {
        return false; // unsupported
    }

    public function hashPassword(Person $person, $plain_password)
    {
        if ($person->password_scheme == 'deskpro3_tech') {
            return sha1($plain_password.$person->salt);
        } else {
            return md5($plain_password.$person->salt);
        }
    }

    public function checkPassword(Person $person, $hashed_password, $plain_password)
    {
        return $hashed_password === $this->hashPassword($person, $plain_password);
    }
}
