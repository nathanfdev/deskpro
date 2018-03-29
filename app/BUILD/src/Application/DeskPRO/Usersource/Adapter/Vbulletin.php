<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

class Vbulletin extends DbTablePhpPasswordCheck
{
    /**
     * @return \Orb\Auth\Adapter\Vbulletin
     */
    protected function _createAuthAdapterObject()
    {
        return new \Orb\Auth\Adapter\Vbulletin($this->getDbAsCallback(), $this->usersource->options);
    }
}
