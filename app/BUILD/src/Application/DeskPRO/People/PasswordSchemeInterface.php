<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\Entity\Person;

interface PasswordSchemeInterface
{
    /**
     * @param string $plain_password
     * @param string $hashed_password
     *
     * @return string
     */
    public function checkInput($plain_password, $hashed_password);

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     * @param string                             $plain_password
     *
     * @return string
     */
    public function hashPassword(Person $person, $plain_password);

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     * @param string                             $hashed_password
     * @param string                             $plain_password
     *
     * @return bool
     */
    public function checkPassword(Person $person, $hashed_password, $plain_password);
}
