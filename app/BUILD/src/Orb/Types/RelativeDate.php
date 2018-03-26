<?php

namespace Orb\Types;

class RelativeDate extends \DateTime
{
    public function __construct($relativeDateOrTime = '', \DateTime $actualDate = null, \DateTimeZone $timezone = null)
    {
        $time     = $actualDate ? $actualDate->getTimestamp() : time();
        $timezone = $timezone ?: ($actualDate ? $actualDate->getTimezone() : null);
        parent::__construct('@'.$time.' '.$relativeDateOrTime, $timezone);
    }
}
