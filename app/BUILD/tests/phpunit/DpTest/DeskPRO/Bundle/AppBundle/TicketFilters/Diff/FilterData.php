<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DpTest\Bundle\AppBundle\TicketFilters\Diff;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Filter;
use DeskPRO\Component\FilterQueryLanguage\Parser;

class FilterData
{
    /**
     * @return Filter[]
     */
    public static function getFilters()
    {
        return [
            // These are the same as the default filters
            self::createFilter(1, [1, 2, 3], "ticket.status = 'awaiting_agent' AND ticket.agent = \$me"),
            self::createFilter(2, [1, 2, 3], "ticket.status = 'awaiting_agent' AND ticket.agent_team IN (\$my_teams)"),
            self::createFilter(3, [1, 2, 3], "ticket.status = 'awaiting_agent' AND ticket.followers HAS \$me"),
            self::createFilter(4, [1, 2, 3], "ticket.status = 'awaiting_agent' AND ticket.agent IS NULL AND ticket.agent_team IS NULL"),
            self::createFilter(5, [1, 2, 3], "ticket.status = 'awaiting_agent'"),

            // These are just for testing
            self::createFilter(100, [1, 2, 3], "ticket.status = 'awaiting_agent' AND ticket.department = 1"),
            self::createFilter(101, [1, 2, 3], "ticket.status = 'awaiting_agent' AND ticket.department = 3"),
        ];
    }

    /**
     * @return Agent[]
     */
    public static function getAgents()
    {
        return [
            self::makeAgent(1, [1, 2, 3], true, true, true),
            self::makeAgent(2, [1, 2], false, true, true),
            self::makeAgent(3, [3], false, true, true),
        ];
    }

    /**
     * @param $id
     * @param $agentIds
     * @param $fql
     *
     * @return Filter
     */
    public static function createFilter($id, $agentIds, $fql)
    {
        $parser = new Parser();

        try {
            $query = $parser->parseQuery($fql);
        } catch (\Exception $e) {
            echo 'Failed arsing: '.$fql;
            echo "\n";
            throw $e;
        }

        $filter         = new Filter();
        $filter->id     = $id;
        $filter->agents = $agentIds;
        $filter->query  = $query;

        return $filter;
    }

    /**
     * @param $id
     * @param $depids
     * @param $all
     * @param $other
     * @param $unassigned
     *
     * @return Agent
     */
    public function makeAgent($id, $depids, $all, $other, $unassigned)
    {
        $agent                      = new Agent();
        $agent->id                  = $id;
        $agent->allowed_departments = $depids;
        if ($all) {
            $agent->all_departments_allowed = true;
        }
        $agent->view_assigned   = $other;
        $agent->view_unassigned = $unassigned;

        return $agent;
    }
}
