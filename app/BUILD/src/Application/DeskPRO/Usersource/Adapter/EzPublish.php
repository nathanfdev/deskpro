<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

class EzPublish extends DbTablePhpPasswordCheck
{
    /**
     * @return \Orb\Auth\Adapter\EzPublish
     */
    protected function _createAuthAdapterObject()
    {
        return new \Orb\Auth\Adapter\EzPublish($this->getDbAsCallback(), $this->usersource->options);
    }
}
