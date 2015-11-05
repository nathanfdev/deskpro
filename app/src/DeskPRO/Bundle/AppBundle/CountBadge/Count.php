<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\CountBadge;

/**
 * Represents a count, typically used to show counters/badges in a UI.
 */
class Count
{
    /**
     * @var int Count value
     */
    private $count = 0;

    /**
     * @var Count[] Nested counts
     */
    private $nested = [];

    /**
     * @var string
     */
    private $grouped_by;

    /**
     * @var string
     */
    private $group;

    /**
     * Make constructor private to allow construction only through factory methods.
     */
    private function __construct()
    {
    }

    /**
     * @param string $grouped_by
     *
     * @return Count
     */
    public static function fromGroupedBy($grouped_by)
    {
        $count = new self();
        $count->setGroupedBy($grouped_by);

        return $count;
    }

    /**
     * @param int $value
     *
     * @return Count
     */
    public static function fromValue($value)
    {
        $count = new self();
        $count->setCount($value);

        return $count;
    }

    /**
     * @param int    $value
     * @param string $group
     *
     * @return Count
     */
    public static function fromValueAndGroup($value, $group)
    {
        $count = new self();
        $count->setCount($value);
        $count->setGroup($group);

        return $count;
    }

    /**
     * @param int    $value
     * @param string $group
     * @param array  $nested
     * @param string $grouped_by
     *
     * @return Count
     */
    public static function create($value, $group, array $nested, $grouped_by = null)
    {
        $count = new self();
        $count->setCount($value);
        $count->setGroup($group);
        $count->setNested($nested);
        $count->setGroupedBy($grouped_by);

        return $count;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @return Count[]
     */
    public function getNested()
    {
        return $this->nested;
    }

    /**
     * @param Count[] $nested
     */
    public function setNested($nested)
    {
        $this->nested = $nested;
    }

    /**
     * @return string
     */
    public function getGroupedBy()
    {
        return $this->grouped_by;
    }

    /**
     * @param string $grouped_by
     */
    public function setGroupedBy($grouped_by)
    {
        $this->grouped_by = $grouped_by;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @param int    $value
     * @param string $group
     * @param bool   $sum_to_value If need to increase $this->value by nested count value
     */
    public function addNested($value, $group, $sum_to_value = false)
    {
        $count = new self();
        $count->setCount($value);
        $count->setGroup($group);
        $this->nested[] = $count;

        if ($sum_to_value) {
            $this->add($value);
        }
    }

    /**
     * @param Count $instance
     * @param bool  $sum_to_value If need to increase $this->value by nested count value
     */
    public function addNestedInstance(Count $instance, $sum_to_value = false)
    {
        $this->nested[] = $instance;
        if ($sum_to_value) {
            $this->add($instance->getCount());
        }
    }

    /**
     * @param int $int
     */
    public function add($int)
    {
        $this->count += $int;
    }
}
