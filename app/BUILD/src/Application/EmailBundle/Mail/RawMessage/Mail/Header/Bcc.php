<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Mail\RawMessage\Mail\Header;

use Zend\Mail\Header\Bcc as BaseBcc;

class Bcc extends BaseBcc
{
    protected $fieldName   = 'Bcc';
    protected static $type = 'bcc';

    public static function fromString($headerLine)
    {
        return AddressListParser::fromString($headerLine, self::$type, new self());
    }
}
