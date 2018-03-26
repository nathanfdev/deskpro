<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Cutter;

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;

class CutterDefFactory
{
    public static function getDef(AbstractReader $reader)
    {
        $def = new Def\Generic();

        return $def;
    }
}
