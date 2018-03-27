<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Security;

use Application\DeskPRO\Entity\Person;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Represends someone logging in via usersources (not just for form logins, this could be renamed).
 */
class DpFormLoginToken extends AbstractToken
{
    protected $credentials;

    /**
     * DpFormLoginToken constructor.
     *
     * @param \Application\DeskPRO\Entity\Person|string $user
     * @param string                                    $credentials
     * @param array                                     $roles
     */
    public function __construct($user, $credentials, array $roles = [])
    {
        parent::__construct($roles);

        try {
            $this->setUser($user);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage().' Got '.json_encode($user));
        }
        $this->credentials = $credentials;

        parent::setAuthenticated($user instanceof Person && count($roles) > 0);
    }

    public function getCredentials()
    {
        return $this->credentials;
    }
}
