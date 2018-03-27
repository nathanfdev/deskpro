<?php

/**
 * Orb.
 *
 * @category Util
 */

namespace Orb\Util;

class TimeUnit
{
    const SECONDS = 'seconds';
    const MINUTES = 'minutes';
    const HOURS   = 'hours';
    const DAYS    = 'days';
    const WEEKS   = 'weeks';
    const MONTHS  = 'months';
    const YEARS   = 'years';

    /**
     * @var string
     */
    private $unit;

    /**
     * @var int
     */
    private $value;

    /**
     * @var int
     */
    private $secs;

    /**
     * @param int    $value
     * @param string $unit
     */
    public function __construct($value, $unit)
    {
        $this->value = $value;
        $this->unit  = $unit;

        $this->secs = Dates::getUnitInSeconds($this->value, $this->unit);
    }

    /**
     * @return int
     */
    public function getSecs()
    {
        return $this->secs;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int|TimeUnit $val
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function equals($val)
    {
        if (is_int($val)) {
            $val_secs = $val;
        } elseif ($val instanceof self) {
            $val_secs = $val->getSecs();
        } else {
            throw new \InvalidArgumentException('Can only compare integers and TimeUnit');
        }

        return $val_secs === $this->secs;
    }

    /**
     * @param int|TimeUnit $val
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    public function compare($val)
    {
        if (is_int($val)) {
            $val_secs = $val;
        } elseif ($val instanceof self) {
            $val_secs = $val->getSecs();
        } else {
            throw new \InvalidArgumentException('Can only compare integers and TimeUnit');
        }

        if ($val_secs === $this->secs) {
            return 0;
        }

        return $val_secs < $this->secs ? -1 : 1;
    }
}
