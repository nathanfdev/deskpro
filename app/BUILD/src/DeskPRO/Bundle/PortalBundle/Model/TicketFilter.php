<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Model;

class TicketFilter
{
    const TYPE_OWN          = 'own';
    const TYPE_ORGANIZATION = 'organization';

    const CATEGORY_AWAITING_AGENT = 'awaiting_agent';
    const CATEGORY_AWAITING_USER  = 'awaiting_user';
    const CATEGORY_RESOLVED       = 'resolved';

    const SORT_USER       = 'user';
    const SORT_AGENT      = 'agent';
    const SORT_CREATED    = 'date_created';
    const SORT_ACTIVITY   = 'date_activity';
    const SORT_LAST_AGENT = 'date_agent';
    const SORT_LAST_USER  = 'date_user';
    const SORT_DEPARTMENT = 'department';
    const SORT_SUBJECT    = 'subject';

    const SORT_DIRECTION_DESC = 'desc';
    const SORT_DIRECTION_ASC  = 'asc';

    /**
     * @var string type "own","organization"
     */
    protected $type;

    /**
     * @var string category "awaiting_agent", "awaiting_person", "resolved"
     */
    protected $category;

    /**
     * @var string sort "activity", "created"
     */
    protected $sort;

    /**
     * @var string sort_direction "desc", "asc"
     */
    protected $sort_direction;

    /**
     * @var string some text to search for in the ticket
     */
    private $search_query;

    public function __construct($type = null, $category = null, $sort = null, $sort_direction = null, $search_query = null)
    {
        $this->setType($type);
        $this->setCategory($category);
        $this->setSort($sort);
        $this->setSortDirection($sort_direction);
        $this->setSearchQuery($search_query);
    }

    /**
     * @param string $search_query
     */
    public function setSearchQuery($search_query)
    {
        $this->search_query = $search_query;
    }

    /**
     * @return string
     */
    public function getSearchQuery()
    {
        return $this->search_query;
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
        if (!in_array($type, [self::TYPE_OWN, self::TYPE_ORGANIZATION])) {
            $type = self::TYPE_OWN;
        }

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        if (!in_array($category, [self::CATEGORY_AWAITING_AGENT, self::CATEGORY_AWAITING_USER, self::CATEGORY_RESOLVED])) {
            $category = self::CATEGORY_AWAITING_USER;
        }

        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param string $sort
     */
    public function setSort($sort)
    {
        if (!in_array($sort, [self::SORT_ACTIVITY, self::SORT_CREATED, self::SORT_DEPARTMENT, self::SORT_SUBJECT, self::SORT_AGENT, self::SORT_LAST_AGENT, self::SORT_LAST_USER, self::SORT_USER])) {
            $sort = self::SORT_ACTIVITY;
        }

        $this->sort = $sort;
    }

    /**
     * @return string
     */
    public function getSortDirection()
    {
        return $this->sort_direction;
    }

    /**
     * @param string $sort_direction
     */
    public function setSortDirection($sort_direction)
    {
        if (!in_array($sort_direction, [self::SORT_DIRECTION_DESC, self::SORT_DIRECTION_ASC])) {
            $sort_direction = self::SORT_DIRECTION_DESC;
        }

        $this->sort_direction = $sort_direction;
    }
}
