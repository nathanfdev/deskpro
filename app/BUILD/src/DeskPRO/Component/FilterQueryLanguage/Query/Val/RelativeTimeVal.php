<?php

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
