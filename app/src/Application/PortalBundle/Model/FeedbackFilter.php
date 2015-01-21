<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Model;


class FeedbackFilter 
{
    const STATUS_ALL = 'all';
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';

    const SORT_DATE = 'date';
    const SORT_POPULARITY = 'most-popular';
    const SORT_COMMENTS = 'most-discussed';
    const SORT_RATING = 'highest-rating';
    const SORT_VIEWS = 'most-views';

    const SORT_DIRECTION_DESC = 'desc';
    const SORT_DIRECTION_ASC = 'asc';

    public static $statuses = array(
        self::STATUS_ALL,
        self::STATUS_CLOSED,
        self::STATUS_ACTIVE
    );

    public static $sorts = array(
        self::SORT_DATE,
        self::SORT_POPULARITY,
        self::SORT_VIEWS,
        self::SORT_COMMENTS,
        self::SORT_RATING
    );

    public static $sort_directions = array(
        self::SORT_DIRECTION_ASC,
        self::SORT_DIRECTION_DESC
    );

    protected $status;
    protected $status_categories;
    protected $types;
    protected $sort;
    protected $sort_direction;

    public function __construct()
    {
        $this->replaceArray(static::getDefaultValues());
    }

    public function toArray()
    {
        return array(
            'status' => $this->getStatus(),
            'status_categories' => $this->getStatusCategories(),
            'types' => $this->getTypes(),
            'sort' => $this->getSort(),
            'sort_direction' => $this->getSortDirection()
        );
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
        return array(
            'status' => static::STATUS_ALL,
            'status_categories' => array(),
            'types' => array(),
            'sort' => static::SORT_DATE,
            'sort_direction' => static::SORT_DIRECTION_DESC
        );
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
