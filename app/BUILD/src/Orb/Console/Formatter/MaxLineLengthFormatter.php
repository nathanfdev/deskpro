<?php

/**
 * DeskPRO.
 */

namespace Orb\Console\Formatter;

use Symfony\Component\Console\Formatter\OutputFormatter;

class MaxLineLengthFormatter extends OutputFormatter
{
    /** @var int */
    public $max_length = 80;

    public function __construct($max_length)
    {
        $this->max_length = (int) $max_length;
    }

    public function format($message)
    {
        $message = parent::format($message);
        $message = wordwrap($message, $this->max_length, "\n");

        return $message;
    }
}
