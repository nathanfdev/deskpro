<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Mail\RawMessage\Mail\Header;

use Zend\Mail\Header\From as BaseFrom;

class From extends BaseFrom
{
    protected $fieldName   = 'From';
    protected static $type = 'from';

    public static function fromString($headerLine)
    {
        return AddressListParser::fromString($headerLine, self::$type, new self());
    }
}
