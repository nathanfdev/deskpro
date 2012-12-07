<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 */

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Application\UserBundle\Form\NewTicketType;
use Application\UserBundle\Form\NewFeedbackType;

class WidgetController extends AbstractController
{
	protected function init()
	{
		$GLOBALS['DP_NON_HELPDESK_SESSION'] = true;

		parent::init();
	}

	public function renderLoginOrPermissionError($return_url = '', $type = 'login')
	{
		if (strpos($this->getRequest()->getRequestUri(), '/chat') !== false) {
			// The login page also has code for no perm if theres already a user sess
			return $this->render('UserBundle:Chat:chat-login.html.twig', array(
				'parent_url' => $this->in->getString('parent_url')
			));
		}

		if ($this->person->getId()) {
			return $this->render('UserBundle:Widget:overlay-perm-error.html.twig', array(
				'parent_url' => $this->in->getString('parent_url')
			));
		}

		return $this->render('UserBundle:Widget:overlay-login.html.twig', array(
			'parent_url' => $this->in->getString('parent_url')
		));
	}

	################################################################################
	# overlay
	################################################################################

	public function overlayAction()
	{
		$lang_id = $this->in->getUint('language_id');

		if ($lang_id && $lang = $this->container->getDataService('Language')->get($lang_id)) {
			$this->person->language = $lang;

			$this->db->beginTransaction();
			try {

				if (!$this->person->isGuest()) {
					$this->em->persist($this->person);
				}

				$this->session->set('language_id', $lang->getId());
				$this->session->save();

				$this->em->flush();
				$this->db->commit();
			} catch (\Exception $e) {
				$this->db->rollback();
				throw $e;
			}

			// Set cookie too so it lasts after session expires
			$cookie = \Application\DeskPRO\HttpFoundation\Cookie::makeCookie('dplid', $lang->getId(), 'never', true);
			$cookie->send();
		}


		#------------------------------
		# New ticket form
		#------------------------------

		$newticket = new \Application\DeskPRO\Tickets\NewTicket\NewTicket(
			Entity\Ticket::CREATED_WEB_PERSON,
			$this->person
		);
		$newticket->setPersonContext($this->person);

		$newticket_formtype = new NewTicketType($this->person);
		$ticketform = $this->get('form.factory')->create($newticket_formtype, $newticket);

		$departments = $this->em->getRepository('DeskPRO:Department')->findAll();

		#------------------------------
		# New idea form
		#------------------------------

		/** @var $structure \Application\DeskPRO\Publish\Structure */
		$structure = $this->container->getSystemService('publish_structure');

		$newfeedback = new \Application\DeskPRO\Feedback\NewFeedback($this->session->getVisitor());
		$newfeedback->setPersonContext($this->person);
		$feedbackform = $this->get('form.factory')->create(new NewFeedbackType($this->person), $newfeedback);

		$feedback_categories = $structure->getFeedbackRootCategories();


		#------------------------------
		# Fetch latest content
		#------------------------------

		$latest_content = new \Application\DeskPRO\Publish\LatestContent($this->em);

		$ds = App::getEntityRepository('DeskPRO:DataStore')->getByName('portal_widget_default_links');
		if ($ds && $ds->getData('selections')) {
			$latest_content->useSelections($ds->getData('selections'));
		}

		$latest_content->setMaxCount(10);

		$chat_active = $this->em->getRepository('DeskPRO:Session')->hasAvailableAgents();
		$website_url = $this->in->getString('website_url');

		$vars = array(
			'parent_url' => $this->in->getString('parent_url'),
			'departments' => $departments,

			'newticket' => $newticket,
			'newticket_formtype' => $newticket_formtype,
			'ticket_options' => $newticket_formtype->getTicketOptions(),
			'ticketform' => $ticketform->createView(),

			'newfeedback' => $newfeedback,
			'feedbackform' => $feedbackform->createView(),
			'feedback_categories' => $feedback_categories,
			'chat_active' => $chat_active,

			'newest_content' => $latest_content->getResults(),
			'website_url' => $website_url,
		);

		return $this->render('UserBundle:Widget:overlay.html.twig', $vars);
	}


	################################################################################
	# new-ticket
	################################################################################

	public function newTicketAction()
	{
		if ($this->person->getId()) {
			$this->ensureRequestToken('newticket_widget');
		}

		$newticket = new \Application\DeskPRO\Tickets\NewTicket\NewTicket(
			Entity\Ticket::CREATED_WEB_PERSON_WIDGET,
			$this->person
		);
		$newticket->setPersonContext($this->person);

		$website_url = $this->in->getString('website_url');
		if ($website_url) {
			$newticket->creation_system_option = $website_url;
		}

		$newticket_formtype = new NewTicketType($this->person);
		$form = $this->get('form.factory')->create($newticket_formtype, $newticket);

		$form->bindRequest($this->get('request'));

		$newticket->ticket->attach_ids = $this->in->getCleanValueArray('attach_ids', 'string', 'discard');
		$newticket->ticket->attach_ids_authed = true;

		$validator = new \Application\UserBundle\Validator\NewTicketValidator();
		$validator->enableWidgetMode();

		if ($validator->isValid($newticket)) {
			$ticket = $newticket->save();
			$person = $ticket['person'];

			App::setSkipCache(true);

			return $this->createJsonResponse(array(
				'ticket_id' => $ticket->id,
				'email' => $newticket->person->email
			));
		} else {
			$errors = $validator->getErrors(true);
			$error_fields = $validator->getErrorGroups(true);

			return $this->createJsonResponse(array(
				'is_error' => true,
				'errors' => $error_fields
			));
		}
	}

	################################################################################
	# new-feedback
	################################################################################

	public function newFeedbackAction()
	{
		$newfeedback = new \Application\DeskPRO\Feedback\NewFeedback($this->session->getVisitor());
		$newfeedback->setPersonContext($this->person);
		$form = $this->get('form.factory')->create(new NewFeedbackType($this->person), $newfeedback);

		$form->bindRequest($this->get('request'));

		$validator = new \Application\UserBundle\Validator\NewFeedbackValidator();

		if ($validator->isValid($newfeedback)) {

			$hash = md5($newfeedback->title . $newfeedback->content . $newfeedback->category_id);

			$dupe = false;
			$person_from_email = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($newfeedback->person_email);
			if ($person_from_email) {
				$datecut = new \DateTime('-20 minutes');
				$exist_feedback = $this->container->getEm()->createQuery("
					SELECT f
					FROM DeskPRO:Feedback f
					WHERE f.person = ?0 AND f.date_created > ?1
				")->setMaxResults(10)->setParameters(array($person_from_email, $datecut))->execute();

				foreach ($exist_feedback as $f) {
					$hash_check = md5($f->title . $f->content . $f->category->getId());
					if ($hash == $hash_check) {
						$dupe = $f->getId();
					}
				}
			}

			if (!$dupe) {
				$feedback = $newfeedback->save();
				$feedback_id = $feedback->getId();
			} else {
				$feedback_id = $dupe;
			}

			App::setSkipCache(true);

			return $this->createJsonResponse(array(
				'feedback_id' => $feedback_id
			));
		} else {
			$errors = $validator->getErrors(true);
			$error_fields = $validator->getErrorGroups(true);

			return $this->createJsonResponse(array(
				'is_error' => true,
				'errors' => $error_fields
			));
		}
	}

	################################################################################
	# chat
	################################################################################

	public function chatAction()
	{
		$sessionObj = $this->get('session');
		$session = $sessionObj->getEntity();

		// User is blocked
		$blocked = $this->em->getRepository('DeskPRO:ChatBlock')->isBlocked($this->getRequest()->getClientIp(), $session->visitor);

		if (!$sessionObj->getPerson()->hasPerm('chat.use')) {
			$blocked = true;
		}

		if ($blocked) {
			$response = $this->createResponse('');
			return $response;
		}

		$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $session));
		$convo = $chat_manager->getChat();

		if ($convo && $convo->status == 'ended') {
			$convo = null;
		}

		$convo_messages = false;
		if ($convo) {
			$convo_messages_obj = $this->em->createQuery("
				SELECT m
				FROM DeskPRO:ChatMessage m
				WHERE m.conversation = ?1 AND m.is_user_hidden = false
				ORDER BY m.id ASC
			")->setParameter(1, $convo)->execute();

			if ($convo_messages_obj) {
				$convo_messages = array();
				foreach ($convo_messages_obj as $obj) {
					$convo_messages[] = $obj->getInfo();
				}
			}
		}

		$is_window = $this->in->getBool('is_window_mode');
		if ($is_window && $convo) {
			$convo->is_window = true;
			$this->db->update('chat_conversations', array('is_window' => 1), array('id' => $convo->getId()));
		}

		$departments = $this->container->getDataService('Department')->getPersonDepartments($sessionObj->getPerson() ?: $this->person, 'chat');

		$current_page = $this->in->getString('parent_url');
		$sessionObj = $this->session;
		if ($current_page && $current_page != $sessionObj->getVisitor()->getLastPage()) {
			$vis = $sessionObj->getVisitor();
			$vis['last_page'] = $current_page;

			if ($session->getIsNew() || !$vis['session_landing_page']) {
				$vis['session_landing_page'] = $current_page;
			}

			if (!$vis['landing_page']) {
				$vis['landing_page'] = $current_page;
			}
			$this->em->persist($vis);
			$this->em->flush();
		}

		$vars = array(
			'parent_url' => $this->in->getString('parent_url'),
			'session_code' => $session->getSessionCode(),
			'convo' => $convo,
			'convo_messages' => $convo_messages,
			'departments' => $departments,
			'initial_name' => $this->in->getString('name'),
			'initial_email' => $this->in->getString('email'),
			'initial_department_id' => $this->in->getUint('department_id'),
			'auto_start' => $this->in->getBool('auto_start'),
			'is_window_mode' => $is_window
		);

		if ($convo) {
			$cookie = new \Application\DeskPRO\HttpFoundation\Cookie('dpchatid', $convo->getId());
		} else {
			$cookie = new \Application\DeskPRO\HttpFoundation\Cookie('dpchatid', 0, time() - 3600);
		}

		$cookie->send();
		return $this->render('UserBundle:Chat:chat.html.twig', $vars);
	}
}
