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
		$channel = 'chat_user_agent.added-as-part';

		$new_chat_cm = new ClientMessage();
		$new_chat_cm->fromArray(array(
			'channel' => $channel,
			'data' => array(
				'conversation_id'   => 20,
				'author_name'       => 'Test',
				'message'           => 'Msg',
			),
			'created_by_client' => 'asdasdsd',
			'for_person'        => App::findEntity('DeskPRO:Person', 20001)
		));

		App::getOrm()->persist($new_chat_cm);
		App::getOrm()->flush();

		exit;
		echo App::getEntityRepository('DeskPRO:Session')->hasAvailableAgents();

		exit;
		return $this->render('DevBundle:Test:test.html.twig');
    }
}
