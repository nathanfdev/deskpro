<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Reader\Item;

class Subject extends Header
{
    /** @var string */
    public $subject;
    /** @var string */
    public $subject_utf8;
    /** @var string */
    public $original_charset;

    public function getSubject()
    {
        return $this->subject;
    }

    public function getSubjectUtf8()
    {
        return $this->subject_utf8;
    }

    public function getOriginalCharset()
    {
        return $this->original_charset;
    }
}
