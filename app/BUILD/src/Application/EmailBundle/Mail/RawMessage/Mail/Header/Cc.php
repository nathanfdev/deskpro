<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Mail\RawMessage\Mail\Header;

use Zend\Mail\Header\Cc as BaseCc;

class Cc extends BaseCc
{
    protected $fieldName   = 'Cc';
    protected static $type = 'cc';

    public static function fromString($headerLine)
    {
        return AddressListParser::fromString($headerLine, self::$type, new self());
    }
}
