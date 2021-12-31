<?php

namespace DeskPRO\Bundle\AppBundle\Security\Authentication\Exception;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class MultipleMatchesException extends BadCredentialsException
{
    /**
     * @var array
     */
    protected $identities;

    /**
     * @return array
     */
    public function getIdentities()
    {
        return $this->identities;
    }

    /**
     * @param array $identities
     *
     * @return $this
     */
    public function setIdentities($identities)
    {
        $this->identities = $identities;

        return $this;
    }
}
