<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Security;

use Application\DeskPRO\Entity\Person;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * This is a token we use if the person is logged in implicitly via being logged in from another interface.
 *
 * For example, if you are unauthenticated in portal, but you are authenticated in agent or admin, then when
 * you visit the portal we have the ability to grant you this token because we can detect those other
 * sessions. See DpTransferSessionAuthListener.
 */
class DpTransferSessionAuthToken extends AbstractToken
{
    protected $session_id;

    public function __construct(Person $person = null, $session_id)
    {
        if ($person) {
            parent::__construct($person->getRoles());
            $this->setUser($person);
        } else {
            parent::__construct([]);
        }

        $this->session_id = $session_id;
        $this->setAuthenticated($person && count($this->getRoles()) > 0);
    }

    public function getCredentials()
    {
        return $this->session_id;
    }
}
