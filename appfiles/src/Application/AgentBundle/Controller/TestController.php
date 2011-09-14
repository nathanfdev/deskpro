<?php

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Orb\Util\Arrays;
use \Orb\Util\Strings;

class TestController extends AbstractController
{
	/**
	 * /agent/test is an actual skeleton page so you can test widgets
	 * etc without loading the full paned interface
	 */
    public function indexAction()
    {
		$vars = array();

		return $this->render('AgentBundle:Test:test.html.twig', $vars);
	}

	/**
	 * This is a test tab that should be loaded into the interface
	 */
	public function tabAction()
	{
		$vars = array();
		return $this->render('AgentBundle:Test:test-tab.html.twig', $vars);
	}
}
