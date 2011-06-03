<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\ClientMessage;

use \Orb\Util\Strings;

class TestController extends Controller
{
    public function indexAction()
    {
		$message = new ClientMessage();
		$message->fromArray(array(
			'for_client' => 171,
			'channel' => 'agent_chat.new-message',
			'data' => array('conversation_id' => 123),
			'created_by_client' => 122,
		));

		App::getOrm()->persist($message);
		App::getOrm()->flush();

		exit;
		return $this->render('DevBundle:Test:test.html.twig');
    }
}
