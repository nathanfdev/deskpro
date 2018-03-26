<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports\Overview;

class TimeTitles
{
    const LAST_TIME_MARKER = 1893456000;

    /** @var array */
    public static $time_phrases = [
        300                    => '< 5 minutes',
        900                    => '5 - 15 minutes',
        1800                   => '15 - 30 minutes',
        3600                   => '30 - 60 minutes',
        7200                   => '1 - 2 hours',
        10800                  => '2 - 3 hours',
        14400                  => '3 - 4 hours',
        21600                  => '4 - 6 hours',
        43200                  => '6 - 12 hours',
        86400                  => '12 - 24 hours',
        172800                 => '1 - 2 days',
        259200                 => '2 - 3 days',
        345600                 => '3 - 4 days',
        432000                 => '4 - 5 days',
        518400                 => '5 - 6 days',
        604800                 => '6 - 7 days',
        1209600                => '1 - 2 weeks',
        1814400                => '2 - 3 weeks',
        2419200                => '3 - 4 weeks',
        4838400                => '1 - 2 months',
        7257600                => '2 - 3 months',
        9676800                => '3 - 4 months',
        12096000               => '4 - 5 months',
        14515200               => '5 - 6 months',
        self::LAST_TIME_MARKER => '> 6 months',
    ];

    /**
     * @param $values
     *
     * @return array
     */
    public static function getValuesArray($values)
    {
        $new_values = [];

        foreach ($values as $group => $v) {
            $new_values[$group] = self::selectTimeGroup($v);
        }

        return $new_values;
    }

    /**
     * @param $time
     *
     * @return string
     */
    public static function selectTimeGroup($time)
    {
        $time_phrases = self::$time_phrases;

        foreach ($time_phrases as $min => $phrase) {
            if ($time <= $min) {
                return $phrase;
            }
        }

        return 'bad time';
    }

    /**
     * @param $field
     *
     * @return string
     */
    public static function makeTimeFieldSelect($field)
    {
        $times = array_keys(self::$time_phrases);

        $sql = 'CASE ';

        $parts = [];
        foreach ($times as $t) {
            $parts[] = " WHEN $field <= $t THEN $t ";
        }

        $sql .= implode('', $parts).' ELSE 0 END AS time_group';

        return $sql;
    }
}
