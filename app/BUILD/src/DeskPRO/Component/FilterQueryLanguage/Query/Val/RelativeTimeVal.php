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

namespace DeskPRO\Component\FilterQueryLanguage\Query\Val;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;

class RelativeTimeVal extends Val
{
    const VAL_TYPE    = Query::VAL_RELATIVE_TIME;
    const MODE_PAST   = 'past';
    const MODE_FUTURE = 'future';

    const UNIT_HOUR  = 'hour';
    const UNIT_DAY   = 'day';
    const UNIT_WEEK  = 'week';
    const UNIT_MONTH = 'month';
    const UNIT_YEAR  = 'year';

    public $mode;

    /**
     * Array of [num, unit].
     *
     * @var array
     */
    public $times;

    /**
     * RelativeTimeVal constructor.
     *
     * @param $mode
     * @param $times
     */
    public function __construct($mode, $times)
    {
        $this->mode  = $mode;
        $this->times = $times;
    }

    /**
     * @param number $num
     * @param string $unit
     *
     * @return array
     */
    public static function makeTime($num, $unit)
    {
        if (
            $unit != self::UNIT_HOUR
            && $unit != self::UNIT_DAY
            && $unit != self::UNIT_WEEK
            && $unit != self::UNIT_MONTH
            && $unit != self::UNIT_YEAR
        ) {
            throw new \InvalidArgumentException('Unknown unit');
        }

        return [$num, $unit];
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'valueType' => self::VAL_TYPE,
            'mode'      => $this->mode,
            'times'     => $this->times,
            'tokenPos'  => $this->tokenPos,
        ];
    }

    /**
     * @param array $props
     *
     * @return RelativeTimeVal
     */
    public static function fromArray(array $props)
    {
        if ($props['valueType'] !== static::VAL_TYPE) {
            throw new \InvalidArgumentException(sprintf('Expected valueType of %s', static::VAL_TYPE));
        }

        $o = new self($props['mode'], $props['times']);
        if (!empty($props['tokenPos'])) {
            $o->tokenPos = $props['tokenPos'];
        }

        return $o;
    }
}
