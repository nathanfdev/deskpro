<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

class Xenforo extends DbTablePhpPasswordCheck
{
    /**
     * @return \Orb\Auth\Adapter\Xenforo
     */
    protected function _createAuthAdapterObject()
    {
        return new \Orb\Auth\Adapter\Xenforo($this->getDbAsCallback(), $this->usersource->options);
    }
}
