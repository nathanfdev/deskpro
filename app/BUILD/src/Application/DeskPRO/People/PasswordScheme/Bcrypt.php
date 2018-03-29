<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People\PasswordScheme;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PasswordSchemeInterface;

require DP_ROOT.'/vendor-src/phpass/PasswordHash.php';

class Bcrypt implements PasswordSchemeInterface
{
    const ITERATIONS = 11;

    public function checkInput($plain_password, $hashed_password)
    {
        $hasher = new \PasswordHash(self::ITERATIONS, false);

        return $hasher->CheckPassword($plain_password, $hashed_password);
    }

    public function hashPassword(Person $person, $plain_password)
    {
        $hasher  = new \PasswordHash(self::ITERATIONS, false);
        $pw_hash = $hasher->HashPassword($plain_password);

        return $pw_hash;
    }

    public function checkPassword(Person $person, $hashed_password, $plain_password)
    {
        $hasher = new \PasswordHash(self::ITERATIONS, false);

        return $hasher->CheckPassword($plain_password, $hashed_password);
    }
}
