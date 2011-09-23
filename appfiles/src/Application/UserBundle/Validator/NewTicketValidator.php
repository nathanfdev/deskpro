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

namespace Application\UserBundle\Validator;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Application\DeskPRO\Tickets\NewTicket\NewTicket;
use Application\DeskPRO\Tickets\NewTicket\PersonProps;
use Application\DeskPRO\Tickets\NewTicket\TicketProps;

use Orb\Util\Arrays;
use Orb\Validator\AbstractValidator;

class NewTicketValidator extends AbstractValidator
{
	protected $run_validators = array();

	/**
	 * @var \Application\DeskPRO\Tickets\NewTicket\NewTicket
	 */
	protected $newticket;

	/**
	 * Check $value to see if its valid.
	 *
	 * @param \Application\DeskPRO\Tickets\NewTicket\NewTicket $newticket
	 * @return bool
	 */
	protected function checkIsValid($newticket)
	{
		$this->newticket = $newticket;

		#------------------------------
		# Validate the department,
		# - If its valid, then we need to get the fields
		#   enabled on it so we know what to validate.
		# - If its invalid, then we can run a number of other
		#   basic validations on message etc too, but not much!
		#------------------------------

		$department_validator = new \Application\DeskPRO\Validator\Department();
		$department_id = $newticket->ticket->department_id;

		if (!$department_validator->isValid($department_id)) {
			$this->addError('ticket.department_id.invalid');
		} else {
			$ticket_display = new \Application\DeskPRO\PageDisplay\Page\TicketPageZoneCollection('user');
			$ticket_display->addPagesFromDb();

			$ticket_page = $ticket_display->getPage($department_id);

			$this->_traverseItems($ticket_page->getPageDisplay('default')->getData());
		}

		#------------------------------
		# Standard ticket fields
		#------------------------------

		$validator = new \Orb\Validator\StringLength(array('min' => 5));
		if (!$validator->isValid($this->newticket->ticket->subject)) {
			$this->addError('ticket.subject.short');
		}

		$validator = new \Orb\Validator\StringLength(array('min' => 10));
		if (!$validator->isValid($this->newticket->ticket->message)) {
			$this->addError('ticket.message.short');
		}

		#------------------------------
		# Standard person fields
		#------------------------------

		$validator = new \Orb\Validator\StringLength(array('min' => 2));
		if (!$validator->isValid($this->newticket->person->name)) {
			$this->addError('person.name.short');
		}

		// Guest
		if (!$this->newticket->person->person_obj) {
			$validator = new \Orb\Validator\StringEmail();
			if (!$validator->isValid($this->newticket->person->email)) {
				$this->addError('person.email.invalid');
			}

		// Logged in user
		} else {
			$email_check = strtolower($this->newticket->person->email);
			$found = false;
			foreach ($this->newticket->person->person_obj->emails as $e) {
				if ($e['email'] == $email_check) {
					$found = true;
					break;
				}
			}

			if (!$found) {
				$this->addError('person.email.not_own');
			}
		}

		if ($this->errors) {
			return false;
		}

		return true;
	}

	protected function _traverseItems(array $items)
	{
		foreach ($items as $item) {
			if ($item['item_type'] == 'group') {
				if (empty($item['items'])) {
					continue;
				}

				$this->_traverseItems($item['items']);
			} else {
				$this->_validateItem($item);
			}
		}
	}

	protected function _validateItem($item)
	{
		switch ($item['item_type']) {
			case 'product':
				$validator = new \Application\DeskPRO\Validator\GenericCategory(array(
					'repository' => App::getEntityRepository('DeskPRO:Product'),
					'allow_none' => true
				));
				if (!$validator->isValid($this->newticket->ticket->category_id)) {
					$this->addError('ticket.product_id');
				}
				break;

			case 'ticket_category':
				$validator = new \Application\DeskPRO\Validator\GenericCategory(array(
					'repository' => App::getEntityRepository('DeskPRO:TicketCategory'),
					'allow_none' => true
				));
				if (!$validator->isValid($this->newticket->ticket->category_id)) {
					$this->addError('ticket.category_id');
				}
				break;

			case 'ticket_priority':
				$validator = new \Application\DeskPRO\Validator\TicketPriority(array(
					'allow_none' => true
				));
				if (!$validator->isValid($this->newticket->ticket->category_id)) {
					$this->addError('ticket.priority_id');
				}
				break;
		}
	}
}
