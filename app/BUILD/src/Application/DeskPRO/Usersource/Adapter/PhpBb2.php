<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

class PhpBb2 extends DbTablePhpPasswordCheck
{
    /**
     * @return \Orb\Auth\Adapter\PhpBb2
     */
    protected function _createAuthAdapterObject()
    {
        return new \Orb\Auth\Adapter\PhpBb2($this->getDbAsCallback(), $this->usersource->options);
    }
}
