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
		$person = App::findEntity('DeskPRO:Person', 20001);

		$cat1 = App::findEntity('DeskPRO:IdeaCategory', 1);
		$cat2 = App::findEntity('DeskPRO:IdeaCategory', 2);
		$cat3 = App::findEntity('DeskPRO:IdeaCategory', 3);
		$cat4 = App::findEntity('DeskPRO:IdeaCategory', 4);
		$cat5 = App::findEntity('DeskPRO:IdeaCategory', 5);

		$active_status1 = App::findEntity('DeskPRO:IdeaStatusCategory', 1);
		$active_status2 = App::findEntity('DeskPRO:IdeaStatusCategory', 2);

		$closed_status1 = App::findEntity('DeskPRO:IdeaStatusCategory', 4);
		$closed_status2 = App::findEntity('DeskPRO:IdeaStatusCategory', 5);

		// Idea
		$comment = new \Application\DeskPRO\Entity\IdeaComment();
		$comment['person'] = $person;
		$comment['content'] = 'This is my idea 1';
		$idea = new \Application\DeskPRO\Entity\Idea();
		$idea->fromArray(array('title' => 'Idea 1',
			'person' => $person,
			'category' => $cat1,
			'status' => 'active',
			'status_category' => $active_status1,
			'first_comment' => $comment
		));
		App::getOrm()->persist($idea);
		App::getOrm()->persist($comment);

		// Idea
		$comment = new \Application\DeskPRO\Entity\IdeaComment();
		$comment['person'] = $person;
		$comment['content'] = 'This is my idea 2';
		$idea = new \Application\DeskPRO\Entity\Idea();
		$idea->fromArray(array('title' => 'Idea 2',
			'person' => $person,
			'category' => $cat1,
			'status' => 'active',
			'status_category' => $active_status2,
			'first_comment' => $comment
		));
		App::getOrm()->persist($idea);
		App::getOrm()->persist($comment);

		// Idea
		$comment = new \Application\DeskPRO\Entity\IdeaComment();
		$comment['person'] = $person;
		$comment['content'] = 'This is my idea 3';
		$idea = new \Application\DeskPRO\Entity\Idea();
		$idea->fromArray(array('title' => 'Idea 3',
			'person' => $person,
			'category' => $cat3,
			'status' => 'closed',
			'status_category' => $closed_status1,
			'first_comment' => $comment
		));
		App::getOrm()->persist($idea);
		App::getOrm()->persist($comment);

		// Idea
		$comment = new \Application\DeskPRO\Entity\IdeaComment();
		$comment['person'] = $person;
		$comment['content'] = 'This is my idea 4';
		$idea = new \Application\DeskPRO\Entity\Idea();
		$idea->fromArray(array('title' => 'Idea 4',
			'person' => $person,
			'category' => $cat4,
			'status' => 'closed',
			'status_category' => $closed_status2,
			'first_comment' => $comment
		));
		App::getOrm()->persist($idea);
		App::getOrm()->persist($comment);

		// Idea
		$comment = new \Application\DeskPRO\Entity\IdeaComment();
		$comment['person'] = $person;
		$comment['content'] = 'This is my idea 5';
		$idea = new \Application\DeskPRO\Entity\Idea();
		$idea->fromArray(array('title' => 'Idea 5',
			'person' => $person,
			'category' => $cat4,
			'status' => 'hidden',
			'status' => 'spam',
			'first_comment' => $comment
		));
		App::getOrm()->persist($idea);
		App::getOrm()->persist($comment);

		// Idea
		$comment = new \Application\DeskPRO\Entity\IdeaComment();
		$comment['person'] = $person;
		$comment['content'] = 'This is my idea 6';
		$idea = new \Application\DeskPRO\Entity\Idea();
		$idea->fromArray(array('title' => 'Idea 6',
			'person' => $person,
			'category' => $cat4,
			'status' => 'hidden',
			'status' => 'deleted',
			'first_comment' => $comment
		));
		App::getOrm()->persist($idea);
		App::getOrm()->persist($comment);

		// Idea
		$comment = new \Application\DeskPRO\Entity\IdeaComment();
		$comment['person'] = $person;
		$comment['content'] = 'This is my idea 7';
		$idea = new \Application\DeskPRO\Entity\Idea();
		$idea->fromArray(array('title' => 'Idea 7',
			'person' => $person,
			'category' => $cat4,
			'status' => 'hidden',
			'status_hidden' => 'validating',
			'first_comment' => $comment
		));
		App::getOrm()->persist($idea);
		App::getOrm()->persist($comment);

		App::getOrm()->flush();


		exit;
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
