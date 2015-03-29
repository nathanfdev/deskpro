<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal;

use Application\DeskPRO\Entity\Person;

class DbalEngineContext
{
    /**
     * @var Person
     */
    protected $agent;

    /**
     * @var int|null
     */
    protected $page;

    /**
     * @var int|null
     */
    protected $per_page;

    /**
     * @var array
     */
    protected $group_by;

    /**
     * @var array
     */
    protected $order_by;

    /**
     * @var string
     */
    protected $and_where;

    public function __construct(Person $person)
    {
        $this->agent = $person;
        $this->group_by = array();
        $this->order_by = array();
    }

    /**
     * @return Person
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param Person $agent
     */
    public function setAgent(Person $agent)
    {
        $this->agent = $agent;
    }

    /**
     * @return int|null
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int|null $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return int|null
     */
    public function getPerPage()
    {
        return $this->per_page;
    }

    /**
     * @param int|null $per_page
     */
    public function setPerPage($per_page)
    {
        $this->per_page = $per_page;
        if (!$this->page) {
            $this->page = 1;
        }
    }

    public function addGroupBy($group)
    {
        $this->group_by[] = $group;
    }

    public function getGroupBy()
    {
        return $this->group_by;
    }

    public function addOrderBy($name, $dir)
    {
        $this->order_by[$name] = strtoupper($dir);
    }

    public function getOrderBy()
    {
        return $this->order_by;
    }

    /**
     * @return string
     */
    public function getAndWhere()
    {
        return $this->and_where;
    }

    /**
     * @param string $where_append
     */
    public function setAndWhere($where_append)
    {
        $this->and_where = $where_append;
    }
}
