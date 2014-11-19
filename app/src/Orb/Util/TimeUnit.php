<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * Orb
 *
 * @package Orb
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
     * @param  int|TimeUnit              $val
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function equals($val)
    {
        if (is_int($val)) {
            $val_secs = $val;
        } elseif ($val instanceof TimeUnit) {
            $val_secs = $val->getSecs();
        } else {
            throw new \InvalidArgumentException("Can only compare integers and TimeUnit");
        }

        return $val_secs === $this->secs;
    }

    /**
     * @param  int|TimeUnit              $val
     * @return int
     * @throws \InvalidArgumentException
     */
    public function compare($val)
    {
        if (is_int($val)) {
            $val_secs = $val;
        } elseif ($val instanceof TimeUnit) {
            $val_secs = $val->getSecs();
        } else {
            throw new \InvalidArgumentException("Can only compare integers and TimeUnit");
        }

        if ($val_secs === $this->secs) {
            return 0;
        }

        return $val_secs < $this->secs ? -1 : 1;
    }
}
