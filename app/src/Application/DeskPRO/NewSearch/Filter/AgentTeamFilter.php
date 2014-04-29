<?php

namespace Application\DeskPRO\NewSearch\Filter;

class AgentTeamFilter extends AbstractFilter
{
    public function getFilter()
    {
        $filter  = null;
        $teamIds = $this->person->getHelper('Agent')->getTeamIds();

        if (!empty($teamIds)) {
            $filter = array('term' => array('agent_team' => $teamIds));
        }

        return $filter;
    }
} 