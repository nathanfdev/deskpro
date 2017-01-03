<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Util;

/**
 * Utility methods working with numbers / math.
 */
class MathUtils
{
    /**
     * @param string $val    The string to parse. Examples: 1B, 4M, 4MB
     * @param mixed  $errVal The return value when the size is invalid. 0, null, or the special string 'exception' to throw an exception
     *
     * @return int
     */
    public static function parseByteSize($val, $errVal = 0)
    {
        if (is_int($val)) {
            return $val;
        }

        if ($val === '-1') {
            return $val;
        }

        if (!is_string($val)) {
            if ($errVal === 'exception') {
                throw new \InvalidArgumentException('Invalid value, expected string');
            }

            return $errVal;
        }

        $val = strtoupper($val);
        $val = preg_replace('#[^a-zA-Z0-9\.\-]#', '', $val);

        if ($val === '') {
            return 0;
        }

        $m = null;
        if (!preg_match('#^(?P<num>\-?\d+(?:\.\d+)?)(?P<unit>\w*)$#', $val, $m)) {
            if ($errVal === 'exception') {
                throw new \InvalidArgumentException('Could not parse value');
            }

            return $errVal;
        }

        $num  = (float) $m['num'];
        $unit = $m['unit'];

        // unitless, means bytes
        if (empty($unit)) {
            return floor($num);
        }

        // Only 1 char, probably a INI-type size like "M" or "G"
        if ($unit !== 'B' && !isset($unit[1])) {
            $unit .= 'B';
        }

        try {
            return self::convertByteUnits($num, $unit);
        } catch (\InvalidArgumentException $e) {
            if ($errVal === 'exception') {
                throw $e;
            }

            return $errVal;
        }
    }

    /**
     * Converts bytes from various units.
     *
     * Known units: B, kB, MB, GB, TB, PB. For binary (base2), use KiB, MiB, GiB, TiB, PiB.
     *
     * @param int    $val
     * @param string $inUnit  The input unit
     * @param string $outUnit The desired output unit TODO only 'B' is supported right now
     *
     * @return int
     */
    public static function convertByteUnits($val, $inUnit, $outUnit = 'B')
    {
        static $pows = [
            'B'  => 0,
            'KB' => 1,
            'MB' => 2,
            'GB' => 3,
            'TB' => 4,
            'PB' => 5,
            'EB' => 6,
            'ZB' => 7,
            'YB' => 8,
        ];

        $inUnit  = strtoupper($inUnit);
        $outUnit = strtoupper($outUnit);

        $base = 1000;
        if ($inUnit[1] === 'I') {
            $base   = 1024;
            $inUnit = $inUnit[0].substr($inUnit, 2);
        }

        if (!isset($pows[$inUnit])) {
            throw new \InvalidArgumentException('Unknown outUnit');
        }

        $bytes = floor($val * pow($base, $pows[$inUnit]));

        if ($outUnit === 'B') {
            return $bytes;
        }

        //TODO support others
    }
}
