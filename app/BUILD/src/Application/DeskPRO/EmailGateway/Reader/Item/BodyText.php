<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Reader\Item;

class BodyText
{
    /** @var array */
    public $raw_parts = [];
    /** @var string */
    public $body;
    /** @var string */
    public $body_utf8;
    /** @var string */
    public $original_charset;

    public function getRawParts()
    {
        return $this->raw_parts;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getBodyUtf8()
    {
        return $this->body_utf8;
    }

    public function getOriginalCharset()
    {
        return $this->original_charset;
    }
}
