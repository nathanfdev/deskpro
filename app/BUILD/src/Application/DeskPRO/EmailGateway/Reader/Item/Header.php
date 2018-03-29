<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Reader\Item;

class Header
{
    /** @var string */
    public $name;
    /** @var string */
    public $header_parts;

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getHeader()
    {
        if (!$this->header_parts) {
            return;
        }

        return $this->header_parts[0];
    }

    /**
     * @return string[]
     */
    public function getAllParts()
    {
        return $this->header_parts;
    }
}
