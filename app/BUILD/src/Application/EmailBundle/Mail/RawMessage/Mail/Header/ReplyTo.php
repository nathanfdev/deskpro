<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Mail\RawMessage\Mail\Header;

use Zend\Mail\Header\ReplyTo as BaseReplyTo;

class ReplyTo extends BaseReplyTo
{
    protected $fieldName   = 'Reply-To';
    protected static $type = 'reply-to';

    public static function fromString($headerLine)
    {
        return AddressListParser::fromString($headerLine, self::$type, new self());
    }
}
