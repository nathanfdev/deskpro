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
		$message = new Entity\ClientMessage();
		$message['created_by_client'] = '123';
		$message['channel'] = 'tickets.new-messages';
		$message['data'] = array('ticket_id' => 3);
		$message['handler_class'] = 'Application\\DeskPRO\\ClientMessage\\MessageHandler\\BasicArray';

		App::getOrm()->persist($message);
		App::getOrm()->flush();

		exit;
    }
}
