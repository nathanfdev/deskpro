<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Reader\Item;

class EmailAddress
{
    /** @var string */
    public $email;
    /** @var string */
    public $name;
    /** @var string */
    public $name_utf8;
    /** @var string */
    public $original_charset;

    public function getRealEmail()
    {
        return $this->email;
    }

    public function getEmail()
    {
        return strtolower($this->email);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNameUtf8()
    {
        return $this->name_utf8;
    }

    public function getOriginalCharset()
    {
        return $this->original_charset;
    }
}
