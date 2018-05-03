<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Cutter\TextPatternCutter;

class TextPattern
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * @param $pattern
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }
}
