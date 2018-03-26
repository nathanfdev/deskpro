<?php

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
