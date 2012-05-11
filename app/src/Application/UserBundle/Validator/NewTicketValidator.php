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
	 * @var array
	 */
	protected $display_fields = array();

	public function setPageData($page_data)
	{
		foreach ($page_data as $i) {
			$this->display_fields[$i['id']] = $i['id'];
		}
	}

	/**
	 * Check $value to see if its valid.
	 *
	 * @param \Application\DeskPRO\Tickets\NewTicket\NewTicket $newticket
	 * @return bool
	 */
	protected function checkIsValid($newticket)
	{
		$edit_mode = false;
		if ($newticket instanceof \Application\DeskPRO\Tickets\EditTicket\EditTicket) {
			$edit_mode = true;
		}

		$this->newticket = $newticket;

		#------------------------------
		# Validate the department,
		# - If its valid, then we need to get the fields
		#   enabled on it so we know what to validate.
		# - If its invalid, then we can run a number of other
		#   basic validations on message etc too, but not much!
		#------------------------------

		if (!$this->display_fields || isset($this->display_fields['ticket_department'])) {
			$department_validator = new \Application\DeskPRO\Validator\Department();
			$department_id = $newticket->ticket->department_id;

			if (!$department_validator->isValid($department_id)) {
				$this->addError('ticket.department_id.invalid');
			} else {
				$ticket_display = new \Application\DeskPRO\PageDisplay\Page\TicketPageZoneCollection('create');
				$ticket_display->addPagesFromDb();

				$ticket_page = $ticket_display->getPage($department_id);

				if ($ticket_page) {
					//$this->_traverseItems($ticket_page);
				}
			}
		}

		#------------------------------
		# Standard ticket fields
		#------------------------------

		if (!$this->display_fields || isset($this->display_fields['ticket_subject'])) {
			$validator = new \Orb\Validator\StringLength(array('min' => 5));
			if (!$validator->isValid($this->newticket->ticket->subject)) {
				$this->addError('ticket.subject.short');
			}
		}

		if (!$edit_mode) {
			if (!$this->display_fields || isset($this->display_fields['ticket_message'])) {
				$validator = new \Orb\Validator\StringLength(array('min' => 10));
				if (!$validator->isValid($this->newticket->ticket->message)) {
					$this->addError('ticket.message.short');
				}
			}
		}

		#------------------------------
		# Standard person fields
		#------------------------------

		if (!$edit_mode) {
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

				// Its new, so check its valid and not in use
				if (!$found) {
					$validator = new \Orb\Validator\StringEmail();
					if (!$validator->isValid($this->newticket->person->email)) {
						$this->addError('person.email.invalid');
					} else {
						// Make sure its not already in use
						$exists = App::getEntityRepository('DeskPRO:PersonEmail')->getEmail($this->newticket->person->email);
						if ($exists) {
							$this->addError('person.email.exists');
						}
					}
				}
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
