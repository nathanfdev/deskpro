<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Mail\RawMessage\Mail\Header;

use Zend\Mail\Header\To as BaseTo;

class To extends BaseTo
{
    protected $fieldName   = 'To';
    protected static $type = 'to';

    public static function fromString($headerLine)
    {
        return AddressListParser::fromString($headerLine, self::$type, new self());
    }
}
