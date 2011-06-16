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
		$active = array(
			'Considering',
			'Planning',
			'Started'
		);

		$closed = array(
			'Completed',
			'Duplicate',
			'Declined',
			'Already Exists'
		);

		foreach ($active as $t) {
			$c = new \Application\DeskPRO\Entity\IdeaStatusCategory();
			$c['status_type'] = 'active';
			$c['title'] = $t;
			App::getOrm()->persist($c);
		}

		foreach ($closed as $t) {
			$c = new \Application\DeskPRO\Entity\IdeaStatusCategory();
			$c['status_type'] = 'closed';
			$c['title'] = $t;
			App::getOrm()->persist($c);
		}
		
		App::getOrm()->flush();

		exit;
		for ($i = 1; $i <= 5; $i++) {
			$c = new \Application\DeskPRO\Entity\IdeaCategory();
			$c['title'] = "Category $i";
			App::getOrm()->persist($c);
		}

		App::getOrm()->flush();

		exit;
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
