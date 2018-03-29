<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

class PhpBb3 extends DbTablePhpPasswordCheck
{
    /**
     * @return \Orb\Auth\Adapter\PhpBb3
     */
    protected function _createAuthAdapterObject()
    {
        return new \Orb\Auth\Adapter\PhpBb3($this->getDbAsCallback(), $this->usersource->options);
    }
}
