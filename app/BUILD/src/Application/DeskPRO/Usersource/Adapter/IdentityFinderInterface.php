<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

interface IdentityFinderInterface
{
    /**
     * Find a user identity just by an email address.
     *
     * Alternatively, this can also return a Person object directly.
     *
     * @param string $input
     *
     * @return \Orb\Auth\Identity|\Application\DeskPRO\Entity\Person|null
     */
    public function findIdentityByInput($input);
}
