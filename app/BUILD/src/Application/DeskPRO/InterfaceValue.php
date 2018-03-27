<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO;

class InterfaceValue
{
    public function getInterface()
    {
        return defined('DP_INTERFACE') ? DP_INTERFACE : 'user';
    }

    public function __toString()
    {
        return $this->getInterface();
    }
}
