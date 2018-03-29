<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

class OsCommerce extends DbTablePhpPasswordCheck
{
    /**
     * @return \Orb\Auth\Adapter\OsCommerce
     */
    protected function _createAuthAdapterObject()
    {
        return new \Orb\Auth\Adapter\OsCommerce($this->getDbAsCallback(), $this->usersource->options);
    }
}
