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
     * @return array|string
     */
    public function getIdentities()
    {
        return $this->identities;
    }

    /**
     * @param array|string $identities
     *
     * @return $this
     */
    public function setIdentities($identities)
    {
        $this->identities = $identities;

        return $this;
    }

    public function serialize()
    {
        return serialize([
            $this->token,
            $this->code,
            $this->message,
            $this->file,
            $this->line,
            $this->identities,
        ]);
    }

    public function unserialize($str)
    {
        list(
            $this->token,
            $this->code,
            $this->message,
            $this->file,
            $this->line,
            $this->identities
            ) = unserialize($str);
    }
}
