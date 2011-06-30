<?php

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Orb\Util\Arrays;
use \Orb\Util\Strings;

class TestController extends AbstractController
{
    public function indexAction()
    {
		$vars = array();

		$agent_names = APp::getEntityRepository('DeskPRO:Person')->getAgentNames();
		$vars['agent_names'] = $agent_names;
		
		return $this->render('AgentBundle:Test:test.html.twig', $vars);
	}
}
