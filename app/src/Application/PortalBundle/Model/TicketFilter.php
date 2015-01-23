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


class TicketFilter 
{
    const TYPE_OWN = 'own';
    const TYPE_ORGANIZATION = 'organization';

    const CATEGORY_AWAITING_AGENT = 'awaiting_agent';
    const CATEGORY_AWAITING_USER = 'awaiting_user';
    const CATEGORY_RESOLVED = 'resolved';

    const SORT_CREATED = 'created';
    const SORT_ACTIVITY = 'activity';

    const SORT_DIRECTION_DESC = 'desc';
    const SORT_DIRECTION_ASC = 'asc';

    /**
     * @var string type "own","organization"
     */
    public $type;

    /**
     * @var string category "awaiting_agent", "awaiting_person", "resolved"
     */
    public $category;

    /**
     * @var string sort "activity", "created"
     */
    public $sort;

    /**
     * @var string sort_direction "desc", "asc"
     */
    public $sort_direction;

    public function __construct($type = null, $category = null, $sort = null, $sort_direction = null)
    {
        $this->setType($type);
        $this->setCategory($category);
        $this->setSort($sort);
        $this->setSortDirection($sort_direction);
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
        if (!in_array($type, array(self::TYPE_OWN, self::TYPE_ORGANIZATION))) {
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
        if (!in_array($category, array(self::CATEGORY_AWAITING_AGENT, self::CATEGORY_AWAITING_USER, self::CATEGORY_RESOLVED))) {
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
        if (!in_array($sort, array(self::SORT_ACTIVITY, self::SORT_CREATED))) {
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
        if (!in_array($sort_direction, array(self::SORT_DIRECTION_DESC, self::SORT_DIRECTION_ASC))) {
            $sort_direction = self::SORT_DIRECTION_DESC;
        }

        $this->sort_direction = $sort_direction;
    }
}
