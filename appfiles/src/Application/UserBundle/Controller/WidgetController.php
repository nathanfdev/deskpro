<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Application\UserBundle\Form\NewTicketType;
use Application\UserBundle\Form\NewFeedbackType;

class WidgetController extends AbstractController
{
	################################################################################
	# overlay
	################################################################################

	public function overlayAction()
	{
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

		$departments = App::getEntityRepository('DeskPRO:Department')->findAll();

		#------------------------------
		# New idea form
		#------------------------------

		/** @var $structure \Application\DeskPRO\Publish\Structure */
		$structure = $this->container->getSystemService('publish_structure');

		$newfeedback = new \Application\DeskPRO\Feedback\NewFeedback(App::getSession()->getVisitor());
		$newfeedback->setPersonContext($this->person);
		$feedbackform = $this->get('form.factory')->create(new NewFeedbackType($this->person), $newfeedback);

		$feedback_categories = $structure->getFeedbackRootCategories();

		#------------------------------
		# Fetch latest content
		#------------------------------

		$latest_content = new \Application\DeskPRO\Publish\LatestContent($this->em);
		$latest_content->setMaxCount(10);

		$vars = array(
			'departments' => $departments,

			'newticket' => $newticket,
			'newticket_formtype' => $newticket_formtype,
			'ticket_options' => $newticket_formtype->getTicketOptions(),
			'ticketform' => $ticketform->createView(),

			'newfeedback' => $newfeedback,
			'feedbackform' => $feedbackform->createView(),
			'feedback_categories' => $feedback_categories,

			'newest_content' => $latest_content->getResults(),
		);

		return $this->render('UserBundle:Widget:overlay.html.twig', $vars);
	}


	################################################################################
	# new-ticket
	################################################################################

	public function newTicketAction()
	{
		$this->ensureRequestToken('newticket_widget');

		$newticket = new \Application\DeskPRO\Tickets\NewTicket\NewTicket(
			Entity\Ticket::CREATED_WEB_PERSON,
			$this->person
		);
		$newticket->setPersonContext($this->person);

		$newticket_formtype = new NewTicketType($this->person);
		$form = $this->get('form.factory')->create($newticket_formtype, $newticket);

		$form->bindRequest($this->get('request'));

		$newticket->ticket->attach_ids = $this->in->getCleanValueArray('attach_ids', 'string', 'discard');
		$newticket->ticket->attach_ids_authed = true;

		$validator = new \Application\UserBundle\Validator\NewTicketValidator();

		if ($validator->isValid($newticket)) {
			$ticket = $newticket->save();
			$person = $ticket['person'];

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
		$newfeedback = new \Application\DeskPRO\Feedback\NewFeedback(App::getSession()->getVisitor());
		$newfeedback->setPersonContext($this->person);
		$form = $this->get('form.factory')->create(new NewFeedbackType($this->person), $newfeedback);

		$form->bindRequest($this->get('request'));

		$validator = new \Application\UserBundle\Validator\NewFeedbackValidator();

		if ($validator->isValid($newfeedback)) {
			$feedback = $newfeedback->save();

			return $this->createJsonResponse(array(
				'feedback_id' => $feedback->id
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
}
