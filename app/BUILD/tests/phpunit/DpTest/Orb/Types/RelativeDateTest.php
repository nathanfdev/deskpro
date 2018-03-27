<?php

namespace DpTest\Orb\Types;

use DpTest\DeskProTestCase;
use Orb\Types\RelativeDate;

class RelativeDateTest extends DeskProTestCase
{
    public function testRelativeFromNow()
    {
        $date  = new RelativeDate('-1 day');
        $check = new \DateTime('yesterday');
        $this->assertEquals($check->format('Y-m-d'), $date->format('Y-m-d'));
    }

    public function testRelativeFromYesterday()
    {
        $from  = new \DateTime('yesterday');
        $date  = new RelativeDate('-1 day', $from);
        $check = new \DateTime('-2 days');
        $this->assertEquals($check->format('Y-m-d'), $date->format('Y-m-d'));
    }
}
