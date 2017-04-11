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

namespace DeskPRO\Bundle\AppBundle\CountBadge;

use JMS\Serializer\Annotation as JMS;

/**
 * Represents a count, typically used to show counters/badges in a UI.
 */
abstract class AbstractCount
{
    /**
     * Count itself.
     *
     * @JMS\Type("integer")
     *
     * @var int Count value
     */
    protected $count = 0;

    /**
     * Legacy identity.
     *
     * @JMS\SerializedName("id")
     * @JMS\Until("20170401")
     *
     * @var mixed
     */
    protected $oldId;

    /**
     * Entity identity.
     *
     * @JMS\Type("integer")
     * @JMS\Since("20170401")
     *
     * @var string
     */
    protected $id;

    /**
     * Entity value.
     *
     * @JMS\Type("string")
     * @JMS\Since("20170401")
     *
     * @var string
     */
    protected $value;

    /**
     * Count type.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $type;

    /**
     * Count title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * Grouping option.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $groupedBy;

    /**
     * Make constructor private to allow construction only through factory methods.
     */
    protected function __construct()
    {
    }

    /**
     * @param string $groupedBy
     *
     * @return Count
     */
    public static function fromGroupedBy($groupedBy)
    {
        $count = new static();
        $count->setGroupedBy($groupedBy);

        return $count;
    }

    /**
     * @param int $value
     *
     * @return Count
     */
    public static function fromValue($value)
    {
        $count = new static();
        $count->setCount($value);

        return $count;
    }

    /**
     * @param int    $value
     * @param string $id
     * @param string $type
     * @param string $title
     * @param string $groupedBy
     *
     * @return Count
     */
    public static function create($value, $id, $type = null, $title = '', $groupedBy = null)
    {
        $count = new static();
        $count->setCount($value);
        $count->setId($id);
        $count->setGroupedBy($groupedBy);
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
        // legacy id representation
        if ($id === null) {
            $this->oldId = 0;
        } else {
            $this->oldId = $id;
        }

        // new id representation
        if (is_numeric($id)) {
            $this->id    = $id;
            $this->value = null;
        } else {
            $this->id    = null;
            $this->value = $id;
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
        return $this->groupedBy;
    }

    /**
     * @param string $groupedBy
     */
    public function setGroupedBy($groupedBy)
    {
        $this->groupedBy = $groupedBy;
    }

    /**
     * @param int $int
     */
    public function add($int)
    {
        $this->count += $int;
    }

    /**
     * @param int    $value
     * @param string $id
     * @param string $type
     * @param string $title
     * @param bool   $sumToValue If need to increase $this->value by nested count value
     *
     * @return $this
     */
    abstract public function addNested($value, $id, $type, $title = '', $sumToValue = false);

    /**
     * @param AbstractCount $instance
     * @param bool          $sumToValue If need to increase $this->value by nested count value
     *
     * @return $this
     */
    abstract public function addNestedInstance(AbstractCount $instance, $sumToValue = false);
}
