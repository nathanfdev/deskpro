<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\People\Helpers;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Orb\Util\Arrays;

/**
 * This helps working with agent teams on a person.
 */
class AgentTeam implements \Orb\Helper\ShortCallableInterface
{
    /** @var \Application\DeskPRO\Entity\Person */
    protected $person;
    /** @var array|null */
    protected $_agent_team_ids = null;

    public function __construct(Entity\Person $person)
    {
        $this->person = $person;
    }

    public function getShortCallableNames()
    {
        return [
            'getAgentTeamIds' => 'getAgentTeamIds',
        ];
    }

    public function getAgentTeamIds()
    {
        if ($this->_agent_team_ids !== null) {
            return $this->_agent_team_ids;
        }

        $this->_agent_team_ids = App::getDb()->fetchAllCol('
            SELECT team_id
            FROM agent_team_members
            WHERE person_id = ?
        ', [$this->person['id']]);

        return $this->_agent_team_ids;
    }

    public function getAgentTeams()
    {
        $ids = $this->getAgentTeamIds();
        if (!$ids) {
            return [];
        }

        $agent_data = App::getContainer()->getAgentData();
        $teams      = [];

        foreach ($ids as $id) {
            $t = $agent_data->getTeam($id);
            if ($t) {
                $teams[] = $t;
            }
        }

        return $teams;
    }

    public function getPrimaryTeamId()
    {
        return $this->person->primaryTeam
            ? $this->person->primaryTeam['id']
            : Arrays::getFirstItem($this->getAgentTeamIds());
    }

    public function addToAgentTeam(Entity\AgentTeam $team)
    {
        return $team->addPerson($this);
    }

    public function reset()
    {
        $this->_agent_team_ids = null;
    }
}
