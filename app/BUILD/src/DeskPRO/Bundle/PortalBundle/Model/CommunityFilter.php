<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Model;

class CommunityFilter
{
    const VIEW_LIST          = 'list';
    const VIEW_STATUS_CHANGE = 'status-change';

    const STATUS_ALL    = 'all';
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';

    const SORT_DATE          = 'date';
    const SORT_POPULARITY    = 'most-popular';
    const SORT_COMMENTS      = 'most-discussed';
    const SORT_RATING        = 'highest-rating';
    const SORT_VIEWS         = 'most-views';
    const SORT_STATUS_CHANGE = 'status-change';

    const SORT_DIRECTION_DESC = 'desc';
    const SORT_DIRECTION_ASC  = 'asc';

    const ACTIVITY_VOTED     = 'voted';
    const ACTIVITY_CREATED   = 'created';
    const ACTIVITY_COMMENTED = 'commented';

    const VIEW_MODE_COMPACT  = 'compact';
    const VIEW_MODE_EXPANDED = 'expanded';

    public static $views = [
        self::VIEW_LIST,
        self::VIEW_STATUS_CHANGE,
    ];

    public static $viewModes = [
        self::VIEW_MODE_COMPACT,
        self::VIEW_MODE_EXPANDED,
    ];

    public static $statuses = [
        self::STATUS_ALL,
        self::STATUS_CLOSED,
        self::STATUS_ACTIVE,
    ];

    public static $activities = [
        self::ACTIVITY_VOTED,
        self::ACTIVITY_CREATED,
        self::ACTIVITY_COMMENTED,
    ];

    public static $views_translated = [
        self::VIEW_LIST          => 'helpcenter.community.view-list',
        self::VIEW_STATUS_CHANGE => 'helpcenter.community.view-status-change',
    ];

    public static $statuses_translated = [
        self::STATUS_ALL    => 'portal.community.status_all',
        self::STATUS_ACTIVE => 'portal.community.status_active',
        self::STATUS_CLOSED => 'portal.community.status_closed',
    ];

    public static $activities_translated = [
        self::ACTIVITY_VOTED     => 'helpcenter.community.activity-voted',
        self::ACTIVITY_CREATED   => 'helpcenter.community.activity-created',
        self::ACTIVITY_COMMENTED => 'helpcenter.community.activity-commented',
    ];

    public static $sorts = [
        self::SORT_POPULARITY,
        self::SORT_RATING,
        self::SORT_DATE,
        self::SORT_COMMENTS,
        self::SORT_VIEWS,
        self::SORT_STATUS_CHANGE,
    ];

    public static $sorts_translated = [
        self::SORT_POPULARITY    => 'portal.community.sort_popularity',
        self::SORT_RATING        => 'portal.community.sort_rating',
        self::SORT_DATE          => 'portal.community.sort_date',
        self::SORT_COMMENTS      => 'portal.community.sort_comments',
        self::SORT_VIEWS         => 'portal.community.sort_views',
        self::SORT_STATUS_CHANGE => 'portal.community.sort_status_change',
    ];

    public static $sort_directions = [
        self::SORT_DIRECTION_ASC,
        self::SORT_DIRECTION_DESC,
    ];

    public static $sort_directions_translated = [
        self::SORT_DIRECTION_ASC  => 'portal.community.dir_asc',
        self::SORT_DIRECTION_DESC => 'portal.community.dir_desc',
    ];

    protected $currentType;
    protected $view;
    protected $status;
    protected $status_categories;
    protected $types;
    protected $sort;
    protected $sort_direction;
    protected $q;
    protected $activityList = [];
    protected $viewMode;

    public function __construct(array $set_these = [])
    {
        $this->replaceArray(array_merge(static::getDefaultValues(), $set_these));
    }

    public function toArray()
    {
        return [
            'view'              => $this->getView(),
            'status'            => $this->getStatus(),
            'status_categories' => $this->getStatusCategories(),
            'types'             => $this->getTypes(),
            'sort'              => $this->getSort(),
            'sort_direction'    => $this->getSortDirection(),
            'q'                 => $this->getQ(),
            'activities'        => $this->getActivities(),
            'view_mode'         => $this->getViewMode(),
        ];
    }

    public function replaceArray(array $filter_values)
    {
        $this->setView($filter_values['view']);
        $this->setStatus($filter_values['status']);
        $this->setStatusCategories($filter_values['status_categories']);
        $this->setTypes($filter_values['types']);
        $this->setSort($filter_values['sort']);
        $this->setSortDirection($filter_values['sort_direction']);
        $this->setQ($filter_values['q']);
        $this->setActivities($filter_values['activities']);
        $this->setViewMode($filter_values['view_mode']);
    }

    public static function getDefaultValues()
    {
        return [
            'view'              => static::VIEW_LIST,
            'status'            => static::STATUS_ACTIVE,
            'status_categories' => [],
            'types'             => [],
            'sort'              => static::SORT_DATE,
            'sort_direction'    => static::SORT_DIRECTION_DESC,
            'q'                 => '',
            'activities'        => [],
            'view_mode'         => self::VIEW_MODE_COMPACT,
        ];
    }

    /**
     * @return mixed
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return string
     */
    public function getQ()
    {
        return $this->q;
    }

    /**
     * @param string $q
     */
    public function setQ($q)
    {
        $this->q = $q;
    }

    /**
     * @return mixed
     */
    public function getViewMode()
    {
        return $this->viewMode;
    }

    /**
     * @param mixed $viewMode
     */
    public function setViewMode($viewMode)
    {
        $this->viewMode = $viewMode;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $view
     */
    public function setView($view)
    {
        if (!in_array($view, static::$views)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid community filter view', $view));
        }

        $this->view = $view;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        if (!in_array($status, static::$statuses)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid community filter status', $status));
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
     * @param string[] $activities
     */
    public function setActivities($activities)
    {
        $this->activityList = $activities;
    }

    /**
     * @return string[]
     */
    public function getActivities()
    {
        return $this->activityList;
    }

    /**
     * @return mixed
     */
    public function getCurrentType()
    {
        return $this->currentType;
    }

    /**
     * @param mixed $currentType
     */
    public function setCurrentType($currentType)
    {
        $this->currentType = $currentType;
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
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid community filter sort', $sort));
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
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid community filter sort direction', $sort_direction));
        }

        $this->sort_direction = $sort_direction;
    }
}
