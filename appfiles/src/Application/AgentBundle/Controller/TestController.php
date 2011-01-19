<?php

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\App;

class TestController extends AbstractController
{
    public function indexAction()
    {
		echo '<pre>';

		$person = $this->person;
		$person->loadHelper('AgentTeam');

		//print_r($person->getHelper('AgentTeam')->getAgentTeamIds());

		print_r($person->getAgentTeamIds());
		//print_r($person['agent_team_ids']);

		exit;
    }
}
