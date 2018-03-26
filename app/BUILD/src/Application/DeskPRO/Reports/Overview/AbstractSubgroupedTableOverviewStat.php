<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports\Overview;

use Orb\Util\Colors;

abstract class AbstractSubgroupedTableOverviewStat extends AbstractTableOverviewStat
{
    /**
     * @var GroupingField
     */
    protected $grouping_field;

    /**
     * @var array
     */
    protected $group_max = null;

    /**
     * @var array
     */
    protected $group_total = null;

    /**
     * @var array
     */
    protected $group_colors = null;

    /**
     * @return string[]
     */
    abstract public function getSubgroupTitles();

    /**
     * @return int[]
     */
    public function getGroupMax()
    {
        $this->_initGroupInfo();

        return $this->group_max;
    }

    /**
     * @return int[]
     */
    public function getGroupTotal()
    {
        $this->_initGroupInfo();

        return $this->group_total;
    }

    /**
     * @return int[]
     */
    public function getGroupColors()
    {
        $this->_initGroupInfo();

        return $this->group_colors;
    }

    protected function _initGroupInfo()
    {
        if (!$this->grouping_field) {
            return;
        }

        if ($this->group_max !== null) {
            return;
        }

        $group_max   = [];
        $group_total = [];

        foreach ($this->getValues() as $master_group => $sub_info) {
            $group_max[$master_group]   = 0;
            $group_total[$master_group] = 0;
            foreach ($sub_info as $count) {
                $group_total[$master_group] += $count;
                if ($count > $group_max[$master_group]) {
                    $group_max[$master_group] = $count;
                }
            }
        }

        $group_colors = Colors::getColorsForKeys(array_keys($this->getSubgroupTitles()));

        $this->group_max    = $group_max;
        $this->group_total  = $group_total;
        $this->group_colors = $group_colors;
    }

    /**
     * @return int
     */
    public function getMax()
    {
        if (!$this->getValues()) {
            return 1;
        }

        if ($this->grouping_field) {
            return max($this->getGroupTotal());
        }

        return max($this->getValues());
    }
}
