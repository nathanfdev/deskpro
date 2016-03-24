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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tasks\Counts;

use JMS\Serializer\Annotation as JMS;

/**
 * Class AgentGrouped.
 */
class AgentGrouped
{
    /**
     * Agent ID this count of tasks attached to.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $agent_id;

    /**
     * Count of tasks assigned to this agent.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $tasks_count;

    /**
     * AgentGrouped constructor.
     *
     * @param int $agent_id
     * @param int $tasks_count
     */
    public function __construct($agent_id, $tasks_count)
    {
        $this->agent_id    = $agent_id;
        $this->tasks_count = $tasks_count;
    }
}
