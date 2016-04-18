<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use JMS\Serializer\Annotation as JMS;

/**
 * Represents a count, typically used to show counters/badges in a UI.
 */
class Count
{
    /**
     * Count itself.
     *
     * @JMS\Type("integer")
     *
     * @var int Count value
     */
    private $count = 0;

    /**
     * Nested counts.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\CountBadge\Count>")
     * @JMS\MaxDepth(0)
     *
     * @var Count[]
     */
    private $nested = [];

    /**
     * Entity identity.
     *
     * @JMS\Type("integer")
     *
     * @var string
     */
    private $id;

    /**
     * Count type.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $type;

    /**
     * Count title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * Grouping option.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $grouped_by;

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
     * @param string $id
     * @param string $type
     * @param string $title
     * @param array  $nested
     * @param string $grouped_by
     *
     * @return Count
     */
    public static function create($value, $id, $type = null, $title = '', $grouped_by = null, array $nested = [])
    {
        $count = new self();
        $count->setCount($value);
        $count->setId($id);
        $count->setNested($nested);
        $count->setGroupedBy($grouped_by);
        $count->setType($type);
        $count->setTitle($title);

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
        $this->count = (int) $count;
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        if ($id === null) {
            $this->id = 0;
        } else {
            $this->id = $id;
        }
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        if ($title === null) {
            $this->title = '';
        } else {
            $this->title = (string) $title;
        }
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
     * @param int    $value
     * @param string $id
     * @param string $type
     * @param string $title
     * @param bool   $sum_to_value If need to increase $this->value by nested count value
     *
     * @return $this
     */
    public function addNested($value, $id, $type, $title = '', $sum_to_value = false)
    {
        $count = new self();
        $count->setCount($value);
        $count->setId($id);
        $count->setType($type);
        $count->setTitle($title);
        $this->nested[] = $count;

        if ($sum_to_value) {
            $this->add($value);
        }

        return $this;
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
        if ($instance->getType()) {
            $this->grouped_by = $instance->getType();
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
