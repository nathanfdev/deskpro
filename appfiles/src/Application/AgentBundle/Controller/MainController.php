<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;

class MainController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('AgentBundle:Main:index.twig.html', array(
			'show_listpane' => $this->person->getPref('agent.ui.show-listpane')
		));
    }
}
