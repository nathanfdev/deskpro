<?php

namespace Application\DeskPRO\Usersource\Actions;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\AgentTeam;

class AddToTeam extends AbstractAction
{
    protected $teamId;

    public function getData()
    {
        return $this->teamId;
    }

    public function setData($value)
    {
        if (!is_numeric($value)) {
            throw new \Exception('Invalid data type for AddToTeam usersource action');
        }

        $this->teamId = (int) $value;
    }

    protected function doHandle(DeskproContainer $container, Person $person, array $rawInput)
    {
        /** @var AgentTeam $teamsRep */
        $teamsRep = $container->getEm()->getRepository('DeskPRO:AgentTeam');
        $teams    = $teamsRep->getTeamsFromIds([$this->getData()]);
        if ($team = reset($teams)) {
            $person->addTeam($team);
        }
    }
}
