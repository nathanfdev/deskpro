<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;

class MainController extends AbstractController
{
    public function indexAction()
    {
		$last_message_id = App::getDb()->fetchColumn("
			SELECT id
			FROM client_messages
			ORDER BY id DESC
			LIMIT 1
		");
		if (!$last_message_id) {
			$last_message_id = -1;
		}

        return $this->render('AgentBundle:Main:index.html.twig', array(
			'show_listpane' => $this->person->getPref('agent.ui.show-listpane'),
			'is_demo' => $this->in->checkIsset('show-demo-bar'),
			'last_message_id' => $last_message_id,
			'js_debug' => App::getConfig('debug.js', array())
		));
    }
}
