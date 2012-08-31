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

use Orb\Util\Arrays;

use Application\UserBundle\Form\EditTicketType;
use Application\UserBundle\Form\NewTicketReplyType;
use Application\UserBundle\Form\NewTicketParticipantType;

class TicketsController extends AbstractController
{
	protected $limited_person = null;

	protected $session_allowed = array();

	protected function init()
	{
		parent::init();

		if ($this->session->get('ticket_access')) {
			$this->session_allowed = $this->session->get('ticket_access');
		}
	}

	################################################################################
	# list
	################################################################################

	/**
	 * View a list of all tickets
	 */
    public function listAction()
    {
		if ($this->person->is_agent) {
			$tickets = $this->em->createQuery("
				SELECT ticket
				FROM DeskPRO:Ticket ticket
				WHERE ticket.person = :person
				ORDER BY ticket.id DESC
			")->execute(array('person' => $this->person));
		} else {
			$tickets = $this->em->createQuery("
				SELECT ticket
				FROM DeskPRO:Ticket ticket
				LEFT JOIN ticket.participants part
				WHERE ticket.person = :person OR part.person = :person
				ORDER BY ticket.id DESC
			")->execute(array('person' => $this->person));
		}

		$active_tickets   = array();
		$resolved_tickets = array();
		$closed_tickets   = array();

		$ticket_ids = array();

		foreach ($tickets as $t) {
			$ticket_ids[] = $t['id'];
			if ($t['status'] == 'awaiting_agent' OR $t['status'] == 'awaiting_user') {
				$active_tickets[] = $t;
			} elseif ($t['status'] == 'resolved') {
				$resolved_tickets[] = $t;
			} else {
				$closed_tickets[] = $t;
			}
		}

		$ticket_ids = implode(',', $ticket_ids);

		$last_messages = array();
		if ($tickets) {
			$last_mesasge_ids = App::getDb()->fetchAllCol("
				SELECT MAX(id)
				FROM tickets_messages
				WHERE ticket_id IN ($ticket_ids)
				GROUP BY ticket_id
			");
			$last_mesasge_ids = implode(',', $last_mesasge_ids);
			if ($last_mesasge_ids) {
				$last_messages = $this->em->createQuery("
					SELECT m, p
					FROM DeskPRO:TicketMessage m
					LEFT JOIN m.person p
					WHERE m.id IN ($last_mesasge_ids)
					GROUP BY m.ticket
					ORDER BY m.id DESC
				")->execute();
			}

			$last_messages = Arrays::keyFromData($last_messages, 'ticket_id');
		}

        return $this->render('UserBundle:Tickets:list.html.twig', array(
			'active_tickets'   => $active_tickets,
			'resolved_tickets' => $resolved_tickets,
			'closed_tickets'   => $closed_tickets,
			'last_messages'    => $last_messages
		));
    }


	################################################################################
	# add-reply
	################################################################################

	/**
	 * Only posted forms get here. The actual form is on the viewtikcet page.
	 *
	 * @param  $ticket_ref
	 */
	public function addReplyAction($ticket_ref)
	{
		$ticket = $this->getTicketOr404($ticket_ref);

		if ($ticket->status == 'closed') {
			return $this->renderLoginOrPermissionError();
		}

		$newreply = new \Application\UserBundle\Tickets\NewReply($ticket, $this->person);
		$form = $this->get('form.factory')->create(new NewTicketReplyType(), $newreply);
		$validator = new \Application\UserBundle\Validator\NewTicketReplyValidator();

		$form->bindRequest($this->get('request'));

		$newreply->attach_ids = $this->in->getCleanValueArray('attach_ids', 'string', 'discard');
		$newreply->attach_ids_authed = true;

		if ($validator->isValid($newreply)) {
			$newreply->save();
			$ticket_message = $newreply->getNewMessage();
		} else {
			$errors = $validator->getErrors(true);
			$error_fields = $validator->getErrorGroups(true);

			return $this->forward('UserBundle:TicketView:load', array(
				'ticket_ref' => $ticket->getPublicId(),
				'display_data' => array(
					'errors' => $errors,
					'error_fields' => $error_fields,
					'newreply' => $newreply,
					'newreply_form' => $form
				)
			));
		}

		return $this->redirectRoute('user_tickets_view', array('ticket_ref' => $ticket->getPublicId()));
	}

	################################################################################
	# manage-participants
	################################################################################

	public function manageParticipantsAction($ticket_ref)
	{
		$ticket = $this->getTicketOr404($ticket_ref);

		$newpart_form = $this->get('form.factory')->create(new NewTicketParticipantType());

		return $this->render('UserBundle:Tickets:manage-participants.html.twig', array(
			'ticket' => $ticket,
			'newpart_form' => $newpart_form->createView()
		));
	}

	################################################################################
	# add-participant
	################################################################################

	public function addParticipantAction($ticket_ref)
	{
		$ticket = $this->getTicketOr404($ticket_ref);

		$newpart = new \Application\UserBundle\Tickets\NewParticipant(
			$ticket
		);

		$newpart_form = $this->get('form.factory')->create(new NewTicketParticipantType(), $newpart);

		if ($this->get('request')->getMethod() == 'POST') {
			$newpart_form->bindRequest($this->get('request'));

			if ($newpart_form->isValid()) {
				$newpart->save();
			}
		}

		return $this->redirectRoute('user_tickets_participants', array('ticket_ref' => $ticket['ref']));
	}

	################################################################################
	# remove-participant
	################################################################################

	public function removeParticipantAction($ticket_ref, $person_id)
	{
		$ticket = $this->getTicketOr404($ticket_ref);

		$ticket->removeParticipant($person_id);

		$this->em->transactional(function($em) use ($ticket) {
			$em->persist($ticket);
			$em->flush();
		});

		return $this->redirectRoute('user_tickets_participants', array('ticket_ref' => $ticket['ref']));
	}

	################################################################################
	# feedback
	################################################################################

	public function feedbackAction($ticket_ref, $message_id)
	{
		$ticket = $this->getTicketOr404($ticket_ref);
		$message = $this->em->find('DeskPRO:TicketMessage', $message_id);

		// Message must be of the correct ticket,
		// must not be a note,
		// must be by an agent
		// must not be rating ourself
		if (!$message OR $message['ticket_id'] != $ticket['id'] OR $message['is_agent_note'] OR !$message['person']['is_agent'] OR $message->person->id == $this->person->id) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Invalid message");
		}

		$feedback = $this->em->getRepository('DeskPRO:TicketFeedback')->getFeedback($message, $this->person, true);

		if ($this->in->getUint('rating')) {
			$feedback->setRating(1);
		}

		return $this->render('UserBundle:Tickets:feedback.html.twig', array(
			'ticket' => $ticket,
			'message' => $message,
			'feedback' => $feedback
		));
	}

	public function feedbackSaveAction($ticket_ref, $message_id)
	{
		$ticket = $this->getTicketOr404($ticket_ref);
		$message = $this->em->find('DeskPRO:TicketMessage', $message_id);

		// Message must be of the correct ticket,
		// must not be a note,
		// must be by an agent
		// must not be rating ourself
		if (!$message OR $message['ticket_id'] != $ticket['id'] OR $message['is_agent_note'] OR !$message['person']['is_agent'] OR $message->person->id == $this->person->id) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Invalid message");
		}

		$feedback = $this->em->getRepository('DeskPRO:TicketFeedback')->getFeedback($message, $this->person, true);
		$feedback['message'] = $this->in->getString('message');
		if ($this->in->getBool('rating')) {
			$feedback->rateUp();
		} else {
			$feedback->rateDown();
		}

		$this->em->transactional(function($em) use ($feedback) {
			$em->persist($feedback);
			$em->flush();
		});

		return $this->render('UserBundle:Tickets:feedback-thank.html.twig', array(
			'ticket' => $ticket,
			'message' => $message,
			'feedback' => $feedback,
		));
	}

	public function feedbackCloseTicketAction($ticket_ref, $message_id)
	{
		$ticket = $this->getTicketOr404($ticket_ref);
		$message = $this->em->find('DeskPRO:TicketMessage', $message_id);

		// Message must be of the correct ticket,
		// must not be a note,
		// must be by an agent
		// must not be rating ourself
		if (!$message OR $message['ticket_id'] != $ticket['id'] OR $message['is_agent_note'] OR !$message['person']['is_agent'] OR $message->person->id == $this->person->id) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Invalid message");
		}

		$feedback = $this->em->getRepository('DeskPRO:TicketFeedback')->getFeedback($message, $this->person, false);

		if (!$feedback) {
			//throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Invalid feedback");
		}

		$ticket->setStatus(Entity\Ticket::STATUS_CLOSED);

		$this->em->transactional(function($em) use ($ticket) {
			$em->persist($ticket);
			$em->flush();
		});

		return $this->render('UserBundle:Tickets:feedback-close.html.twig', array(
			'ticket' => $ticket,
			'message' => $message,
			'feedback' => $feedback,
			'close_window' => $this->in->getBool('close_win')
		));
	}

	public function resolveAction($ticket_ref)
	{
		$ticket  = $this->getTicketOr404($ticket_ref);
		$message = $this->em->getRepository('DeskPRO:TicketMessage')->getLastAgentReply($ticket);
		$exist_feedback = null;
		$no_feedback = false;

		if (!$message || $message->person->id == $this->person->id) {
			$no_feedback = true;
		} else {
			$exist_feedback = $this->em->getRepository('DeskPRO:TicketFeedback')->getFeedback($message, $this->person, false);
		}

		if ($this->in->getBool('process')) {
			$feedback = false;
			if (!$no_feedback and $this->in->getBool('with_feedback')) {
				if ($exist_feedback) {
					$feedback = $exist_feedback;
				} else {
					$feedback = new \Application\DeskPRO\Entity\TicketFeedback();
					$feedback->ticket = $message->ticket;
					$feedback->ticket_message = $message;
					$feedback->person = $this->person;
				}

				$feedback->message = $this->in->getString('message');
				$feedback->rating  = $this->in->getInt('rating');
			}

			$ticket->setStatus('resolved');

			$this->em->transactional(function ($em) use ($ticket, $feedback) {
				if ($feedback) {
					$em->persist($feedback);
				}
				$em->persist($ticket);
				$em->flush();
			});

			return $this->redirectRoute('user_tickets_view', array('ticket_ref' => $ticket->getPublicId()));
		}

		return $this->render('UserBundle:Tickets:resolve.html.twig', array(
			'ticket' => $ticket,
			'message' => $message,
			'exist_feedback' => $exist_feedback,
			'no_feedback' => $no_feedback,
		));
	}


	/**
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	protected function getTicketOr404($ticket_ref, $authcode = null)
	{
		if (ctype_digit($ticket_ref)) {
			$ticket = $this->em->getRepository('DeskPRO:Ticket')->findOneById($ticket_ref);
		} else {
			$ticket = $this->em->getRepository('DeskPRO:Ticket')->findOneByRef($ticket_ref);
		}

		/** @var $ticket \Application\DeskPRO\Entity\Ticket */

		if (!$ticket OR ($ticket['person_id'] != $this->person['id'] AND !isset($this->session_allowed[$ticket['id']])) AND !$ticket->hasParticipantPerson($this->person)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no ticket with ID $ticket_ref");
		}

		if (isset($this->session_allowed[$ticket['id']])) {
			$person = $this->em->getRepository('DeskPRO:Person')->find($this->session_allowed[$ticket['id']]['person_id']);

			// Set the current person context
			if ($person['is_user'] AND $this->person != $person) {
				$this->person = $person;
				App::setCurrentPerson($person);
			} else {
				$this->limited_person = $person;
			}
		}

		return $ticket;
	}
}
