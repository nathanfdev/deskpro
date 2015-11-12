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
namespace DeskPRO\Bundle\AppBundle\Model;

/**
 * Class TaskFilter.
 */
class TaskFilter
{
    const GROUP_ALL        = 'all';
    const GROUP_MY         = 'my';
    const GROUP_TEAM       = 'team';
    const GROUP_DEPARTMENT = 'department';
    const GROUP_DELEGATED  = 'delegated';
    const GROUP_UNASSIGNED = 'unassigned';

    /**
     * @var string
     */
    private $group;

    /**
     * @var int[]
     */
    private $agent_ids;

    /**
     * @var int[]
     */
    private $project_ids;

    /**
     * @var string[]
     */
    private $labels;

    /**
     * @var string
     */
    private $sort;

    /**
     * @var string
     */
    private $sort_direction;

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
     * @return \int[]
     */
    public function getAgentIds()
    {
        return $this->agent_ids;
    }

    /**
     * @param \int[] $agent_ids
     */
    public function setAgentIds($agent_ids)
    {
        $this->agent_ids = $agent_ids;
    }

    /**
     * @return \int[]
     */
    public function getProjectIds()
    {
        return $this->project_ids;
    }

    /**
     * @param \int[] $project_ids
     */
    public function setProjectIds($project_ids)
    {
        $this->project_ids = $project_ids;
    }

    /**
     * @return \string[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param \string[] $labels
     */
    public function setLabels($labels)
    {
        $this->labels = $labels;
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
        $this->sort_direction = $sort_direction;
    }
}
