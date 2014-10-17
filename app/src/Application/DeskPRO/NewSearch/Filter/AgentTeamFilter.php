<?php

namespace Application\DeskPRO\NewSearch\Filter;

use Elastica\Filter;

class AgentTeamFilter extends AbstractFilter
{
    public function getFilter()
    {
        $teamIds = $this->person->getHelper('Agent')->getTeamIds();

        if (!empty($teamIds)) {
			$filter = new Filter\Terms('agent_team', $teamIds);
			return $filter->toArray();
        } else {
			$filter = new Filter\Terms('agent_team', array(-1));
			return $filter->toArray();
		}
    }
} 