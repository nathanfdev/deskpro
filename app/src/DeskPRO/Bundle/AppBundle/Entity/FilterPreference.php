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

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;

class FilterPreference
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var FilterView
     */
    protected $filter_view;

    /**
     * @var Person
     */
    protected $agent;

    /**
     * @var int
     */
    protected $display_order;

    /**
     * @var string
     */
    protected $main_grouping;

    /**
     * @var string
     */
    protected $result_grouping;

    /**
     * @var bool
     */
    protected $show_sla;

    public function __construct()
    {
        $this->display_order = 0;
        $this->show_sla = false;
    }

    /**
     * @return Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param Filter $filter
     */
    public function setFilter(Filter $filter)
    {
        $this->filter = $filter;
        $filter->addFilterPreference($this);
    }

    /**
     * @return FilterView
     */
    public function getFilterView()
    {
        return $this->filter_view;
    }

    /**
     * @param FilterView $filter_view
     */
    public function setFilterView(FilterView $filter_view)
    {
        $this->filter_view = $filter_view;
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
    public function setAgent(Person $agent = null)
    {
        $this->agent = $agent;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param int $display_order
     */
    public function setDisplayOrder($display_order)
    {
        $this->display_order = (int)$display_order;
    }

    /**
     * @return array
     */
    public function getMainGrouping()
    {
        return $this->main_grouping;
    }

    /**
     * @param string $main_grouping
     */
    public function setMainGrouping($main_grouping)
    {
        $this->main_grouping = $main_grouping;
    }

    /**
     * @return array
     */
    public function getResultGrouping()
    {
        return $this->result_grouping;
    }

    /**
     * @param string $result_grouping
     */
    public function setResultGrouping($result_grouping)
    {
        $this->result_grouping = $result_grouping;
    }

    /**
     * @return boolean
     */
    public function hasShowSla()
    {
        return $this->show_sla;
    }

    /**
     * @param boolean $show_sla
     */
    public function setShowSla($show_sla)
    {
        $this->show_sla = (bool)$show_sla;
    }

    public function isPrivate()
    {
        return null !== $this->agent;
    }
}
