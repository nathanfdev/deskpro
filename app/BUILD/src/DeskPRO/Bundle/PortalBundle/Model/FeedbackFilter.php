<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Model;

class FeedbackFilter
{
    const STATUS_ALL    = 'all';
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';

    const SORT_DATE       = 'date';
    const SORT_POPULARITY = 'most-popular';
    const SORT_COMMENTS   = 'most-discussed';
    const SORT_RATING     = 'highest-rating';
    const SORT_VIEWS      = 'most-views';

    const SORT_DIRECTION_DESC = 'desc';
    const SORT_DIRECTION_ASC  = 'asc';

    public static $statuses = [
        self::STATUS_ALL,
        self::STATUS_CLOSED,
        self::STATUS_ACTIVE,
    ];

    public static $statuses_translated = [
        self::STATUS_ALL    => 'portal.feedback.status_all',
        self::STATUS_ACTIVE => 'portal.feedback.status_active',
        self::STATUS_CLOSED => 'portal.feedback.status_closed',
    ];

    public static $sorts = [
        self::SORT_POPULARITY,
        self::SORT_RATING,
        self::SORT_DATE,
        self::SORT_COMMENTS,
        self::SORT_VIEWS,
    ];

    public static $sorts_translated = [
        self::SORT_POPULARITY => 'portal.feedback.sort_popularity',
        self::SORT_RATING     => 'portal.feedback.sort_rating',
        self::SORT_DATE       => 'portal.feedback.sort_date',
        self::SORT_COMMENTS   => 'portal.feedback.sort_comments',
        self::SORT_VIEWS      => 'portal.feedback.sort_views',
    ];

    public static $sort_directions = [
        self::SORT_DIRECTION_ASC,
        self::SORT_DIRECTION_DESC,
    ];

    public static $sort_directions_translated = [
        self::SORT_DIRECTION_ASC  => 'portal.feedback.dir_asc',
        self::SORT_DIRECTION_DESC => 'portal.feedback.dir_desc',
    ];

    protected $status;
    protected $status_categories;
    protected $types;
    protected $sort;
    protected $sort_direction;

    public function __construct(array $set_these = [])
    {
        $this->replaceArray(array_merge(static::getDefaultValues(), $set_these));
    }

    public function toArray()
    {
        return [
            'status'            => $this->getStatus(),
            'status_categories' => $this->getStatusCategories(),
            'types'             => $this->getTypes(),
            'sort'              => $this->getSort(),
            'sort_direction'    => $this->getSortDirection(),
        ];
    }

    public function replaceArray(array $filter_values)
    {
        $this->setStatus($filter_values['status']);
        $this->setStatusCategories($filter_values['status_categories']);
        $this->setTypes($filter_values['types']);
        $this->setSort($filter_values['sort']);
        $this->setSortDirection($filter_values['sort_direction']);
    }

    public static function getDefaultValues()
    {
        return [
            'status'            => static::STATUS_ACTIVE,
            'status_categories' => [],
            'types'             => [],
            'sort'              => static::SORT_DATE,
            'sort_direction'    => static::SORT_DIRECTION_DESC,
        ];
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        if (!in_array($status, static::$statuses)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid feedback filter status', $status));
        }

        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatusCategories()
    {
        return $this->status_categories;
    }

    /**
     * @param mixed $status_categories
     */
    public function setStatusCategories($status_categories)
    {
        $this->status_categories = $status_categories;
    }

    /**
     * @return mixed
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param mixed $types
     */
    public function setTypes($types)
    {
        $this->types = $types;
    }

    /**
     * @return mixed
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param mixed $sort
     */
    public function setSort($sort)
    {
        if (!in_array($sort, static::$sorts)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid feedback filter sort', $sort));
        }

        $this->sort = $sort;
    }

    /**
     * @return mixed
     */
    public function getSortDirection()
    {
        return $this->sort_direction;
    }

    /**
     * @param mixed $sort_direction
     */
    public function setSortDirection($sort_direction)
    {
        if (!in_array($sort_direction, static::$sort_directions)
        ) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid feedback filter sort direction', $sort_direction));
        }

        $this->sort_direction = $sort_direction;
    }
}
