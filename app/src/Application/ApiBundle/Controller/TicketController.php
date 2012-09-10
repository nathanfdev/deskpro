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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\Entity\Ticket AS Ticket;


class TicketController extends AbstractController
{
	public function getTicketAction($ticket_id)
	{
		$ticket = $this->_getTicketOr404($ticket_id);

		return $this->createApiResponse(array('ticket' => $ticket->toApiData()));
	}

	public function postTicketAction($ticket_id)
	{
		$ticket = $this->_getTicketOr404($ticket_id, 'edit');

		$fields = array(
			'department_id' => 'Uint',
			'language_id' => 'Uint',
			'agent_id' => 'Uint',
			'agent_team_id' => 'Uint',
			'product_id' => 'Uint',
			'priority_id' => 'Uint',
			'workflow_id' => 'Uint',
			'language_id' => 'String',
			'is_hold' => 'Boolean',
			'flag' => 'string',
			'urgency' => 'Uint',
		);
		$actions = array();

		foreach ($fields AS $field => $cleanType) {
			if ($this->in->checkIsset($field)) {
				$actions[$field] = $this->in->{'get' . $cleanType}($field);
			}
		}

		$editor = App::getApi('tickets')->getTicketEditor($ticket);
		$editor->setPersonContext($this->person);
		$editor->applyActions($actions);

		$this->db->beginTransaction();
		try {
			$this->em->persist($ticket);

			if ($this->person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {
				if (!empty($_POST['fields'])) {
					$post_custom_fields = $this->request->request->get('fields', array());
					if (!empty($post_custom_fields)) {
						$field_manager = $this->container->getSystemService('ticket_fields_manager');
						$field_manager->saveFormToObject($post_custom_fields, $ticket, true);
						$this->em->persist($ticket);
					}
				}
			}

			$this->em->flush();

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createSuccessResponse();
	}

	public function deleteTicketAction($ticket_id)
	{
		$ticket = $this->_getTicketOr404($ticket_id, 'delete');

		$this->em->getConnection()->beginTransaction();

		try {
			$ticket->setStatus('hidden.deleted');
			$this->em->flush();
			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		$this->db->insert('tickets_deleted', array(
			'ticket_id' => $ticket->id,
			'by_person_id' => $this->person->id,
			'new_ticket_id' => 0,
			'reason' => $this->in->getString('reason'),
			'date_created' => date('Y-m-d H:i:s')
		));

		if ($this->in->getBool('ban')) {
			foreach ($ticket->person->emails as $email) {
				$email_addy = strtolower($email->email);
				App::getDb()->replace('ban_emails', array(
					'banned_email' => $email_addy,
					'is_pattern' => 0
				));
			}
		}

		return $this->createSuccessResponse();
	}

	public function replyTicketAction($ticket_id)
	{
		$ticket = $this->_getTicketOr404($ticket_id, 'reply');

		$message = new \Application\DeskPRO\Entity\TicketMessage();
		$message['ticket'] = $ticket;
		$message['person'] = $this->person;
		$message['ip_address'] = $this->request->getClientIp();
		$message['creation_system'] = 'web.api';
		$message->setMessageText($this->in->getString('message'));

		if ($this->in->getBool('note')) {
			$message['is_agent_note'] = true;
		}

		if ($dupe_message = $this->em->getRepository('DeskPRO:TicketMessage')->checkDupeMessage($message, $ticket)) {
			return $this->createApiResponse(array(
				'dupe_message' => true,
				'message_id' => $dupe_message['id'],
				'time' => $dupe_message->date_created->getTimestamp()
			));
		}

		$ticket->addMessage($message);

		if ($this->in->getBool('suppress_user_notify')) {
			$ticket->getTicketLogger()->recordExtra('suppress_user_notify', true);
		}

		$this->db->beginTransaction();

		try {
			$this->em->persist($ticket);
			$this->em->flush();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createSuccessResponse();
	}

	public function spamTicketAction($ticket_id)
	{
		$ticket = $this->_getTicketOr404($ticket_id, 'delete');

		$this->em->getConnection()->beginTransaction();

		try {
			$ticket->setStatus('hidden.spam');
			$this->em->flush();
			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		if ($this->in->getBool('ban')) {
			foreach ($ticket->person->emails as $email) {
				$email_addy = strtolower($email->email);
				App::getDb()->replace('ban_emails', array(
					'banned_email' => $email_addy,
					'is_pattern' => 0
				));
			}
		}

		return $this->createSuccessResponse();
	}

	public function postLockAction($ticket_id)
	{
		$ticket = $this->_getTicketOr404($ticket_id);

		if ($ticket->hasLock()) {
			return $this->createApiErrorResponse('action.impossible', 'Ticket already locked');
		}

		$ticket->setLockedByAgent($this->person);
		$this->em->persist($ticket);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	public function postUnlockAction($ticket_id)
	{
		$ticket = $this->_getTicketOr404($ticket_id);

		if ($ticket->hasLock()) {
			$ticket->setLockedByAgent(null);
			$this->em->persist($ticket);
			$this->em->flush();
		}

		return $this->createSuccessResponse();
	}

	public function getParticipantsAction($ticket_id)
	{
		$ticket = $this->_getTicketOr404($ticket_id);

		return $this->createApiResponse(array('participants' => $this->getApiData($ticket->participants)));
	}

	public function postParticipantsAction($ticket_id)
	{
		$ticket = $this->_getTicketOr404($ticket_id, 'modify_cc');

		$person = null;
		if ($this->in->getUint('person_id')) {
			$person = $this->em->find('DeskPRO:Person', $this->in->getUint('person_id'));
		} elseif ($email_address = $this->in->getString('email_address')) {
			if (!\Orb\Validator\StringEmail::isValueValid($email_address)) {
				return $this->createApiErrorResponse('invalid_email', 'Invalid email address');
			}

			$person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email_address);

			if (!$person) {
				$person = new \Application\DeskPRO\Entity\Person();
				$person->setEmail($email_address);
			}
		}

		if (!$person) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		if ($person->id && $ticket->hasParticipantPerson($person)) {
			return $this->createSuccessResponse();
		}

		$this->db->beginTransaction();

		try {

			if (!$person->id) {
				$this->em->persist($person);
				$this->em->flush();
			}

			$part = $ticket->addParticipantPerson($person);
			$this->em->persist($part);
			$this->em->persist($ticket);
			$this->em->flush();

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createSuccessResponse();
	}

	public function deleteParticipantAction($ticket_id, $person_id)
	{
		$ticket = $this->_getTicketOr404($ticket_id, 'modify_cc');
		$person = $this->em->find('DeskPRO:Person', $person_id);

		if (!$person) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$part = $this->em->createQuery("
			SELECT part
			FROM DeskPRO:TicketParticipant part
			WHERE part.ticket = ?0 AND part.person = ?1
		")->setParameters(array($ticket, $person))->setMaxResults(1)->getOneOrNullResult();

		if (!$part) {
			return $this->createSuccessResponse();
		}

		$this->db->beginTransaction();

		try {
			$this->em->remove($part);
			$this->em->flush();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createSuccessResponse();
	}

	public function postLabelsAction($ticket_id)
	{
		$ticket = $this->_getTicketOr404($ticket_id, 'modify_labels');
		$label = $this->in->getString('label');

		if ($label === '') {
			return $this->createApiErrorResponse('required_field', "Field 'label' missing or empty");
		}

		$ticket->getLabelManager()->addLabel($label);
		$this->em->persist($ticket);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	public function deleteLabelsAction($ticket_id, $label)
	{
		$ticket = $this->_getTicketOr404($ticket_id, 'modify_labels');

		$ticket->getLabelManager()->removeLabel($label);
		$this->em->persist($ticket);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	/**
	 * @param integer $id
	 * @return \Application\DeskPRO\Entity\Ticket
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function _getTicketOr404($id, $check_perm = null)
	{
		$q = $this->em->createQuery("SELECT t FROM DeskPRO:Ticket t WHERE t.id = ?0");
		$q->setFetchMode('DeskPRO:Person', 'person', 'EAGER');
		$q->setFetchMode('DeskPRO:Person', 'agent', 'EAGER');
		$q->setParameters(array($id));

		$ticket = $q->getOneOrNullResult();

		if (!$ticket || !$this->person->PermissionsManager->TicketChecker->canView($ticket)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no ticket with ID $id");
		}

		if ($check_perm && !$this->checkTicketPerm($ticket, $check_perm)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no ticket with ID $id");
		}

		return $ticket;
	}

	public function checkTicketPerm(Ticket $ticket, $check_perm)
	{
		if (strpos($check_perm, 'modify_') === 0) {
			$check_perm = str_replace('modify_', '', $check_perm);
			if (!$this->person->PermissionsManager->TicketChecker->canModify($ticket, $check_perm)) {
				return false;
			}
		} elseif ($check_perm == 'delete') {
			if (!$this->person->PermissionsManager->TicketChecker->canDelete($ticket)) {
				return false;
			}
		} elseif ($check_perm == 'reply') {
			if (!$this->person->PermissionsManager->TicketChecker->canReply($ticket)) {
				return false;
			}
		}

		return true;
	}
}
