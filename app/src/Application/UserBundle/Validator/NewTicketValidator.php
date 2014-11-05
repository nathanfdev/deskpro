<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
use Application\DeskPRO\Form\Captcha\CaptchaAbstract;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutDisplay;
use Application\DeskPRO\TicketLayout\LayoutField;
use Orb\Validator\AbstractValidator;

class NewTicketValidator extends AbstractValidator
{
	/** @var array */
	protected $run_validators = array();

	/**
	 * @var \Application\DeskPRO\Tickets\NewTicket\NewTicket
	 */
	protected $newticket;

	/**
	 * @var array
	 */
	protected $display_fields = array();

	/**
	 * @var \Application\DeskPRO\Form\Captcha\CaptchaAbstract
	 */
	protected $captca;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $mock_ticket;

	/**
	 * @var bool
	 */
	protected $widget_mode = false;

	/**
	 * @var bool
	 */
	protected $edit_mode = false;

	public function enableWidgetMode()
	{
		$this->widget_mode = true;
	}

	public function enableEditMode()
	{
		$this->edit_mode = true;
	}


	/**
	 * @param Layout $layout
	 */
	public function setLayout(Layout $layout)
	{
		foreach ($layout as $field) {
			$this->display_fields[$field->getId()] = $field;
		}
	}

	/**
	 * @param CaptchaAbstract $captcha
	 */
	public function setCaptcha(CaptchaAbstract $captcha)
	{
		$this->captca = $captcha;
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

		$this->mock_ticket = new \Application\DeskPRO\Entity\Ticket(false);
		if ($newticket->ticket->department_id) {
			$this->mock_ticket->setDepartmentId($newticket->ticket->department_id);
		}
		if ($newticket->ticket->category_id) {
			$this->mock_ticket->setCategoryId($newticket->ticket->category_id);
		}
		if ($newticket->ticket->product_id) {
			$this->mock_ticket->setProductId($newticket->ticket->product_id);
		}
		if ($newticket->ticket->priority_id) {
			$this->mock_ticket->setPriorityId($newticket->ticket->priority_id);
		}

		#------------------------------
		# Validate the department,
		# - If its valid, then we need to get the fields
		#   enabled on it so we know what to validate.
		# - If its invalid, then we can run a number of other
		#   basic validations on message etc too, but not much!
		#------------------------------

		if (!$this->display_fields || isset($this->display_fields['department'])) {
			$department_validator = new \Application\DeskPRO\Validator\Department();

			$department_id = $newticket->ticket->department_id;

			if (isset($this->display_fields['department']) && !$department_validator->isValid($department_id)) {
				$this->addError('ticket.department_id.invalid');
			} else {

				if ($this->widget_mode) {
					$layout = new Layout();
					$layout->add(new LayoutField('user_name'));
					$layout->add(new LayoutField('department'));
					$layout->add(new LayoutField('subject'));
					$layout->add(new LayoutField('message'));
					$layout->add(new LayoutField('attachments'));
				} else {
					$layout = App::$container->getTicketLayoutManager()->getUserLayouts()->getLayout($department_id);
					$layout = LayoutDisplay::createFromLayout($layout, $this->edit_mode ? LayoutDisplay::EDIT_TICKET : LayoutDisplay::NEW_TICKET);
				}

				if ($layout) {
					$this->setLayout($layout);
				}
			}
		}

		#------------------------------
		# Standard ticket fields
		#------------------------------

		if (!$this->display_fields || isset($this->display_fields['subject'])) {
			$validator = new \Orb\Validator\StringLength(array('min' => 5));
			if (!$validator->isValid($this->newticket->ticket->subject)) {
				$this->addError('ticket.subject.short');
			}
		}

		if (!$edit_mode) {
			if (!$this->display_fields || isset($this->display_fields['message'])) {
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
			// Guest
			if (!$this->newticket->person->person_obj) {
				$validator = new \Orb\Validator\StringEmail();
				if (!$validator->isValid($this->newticket->person->email)) {
					$this->addError('person.email.invalid');
				} else {
					$exists = App::getEntityRepository('DeskPRO:PersonEmail')->getEmail($this->newticket->person->email);
					if ($exists && $exists->person && $exists->person->is_disabled) {
						$this->addError('person.email.account_disabled', 'account_disabled');
					} elseif (!App::getSystemService('email_address_validator')->isValidUserEmail($this->newticket->person->email)) {
						$this->addError('person.email.invalid');
					}
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
					} elseif (!$this->widget_mode) {
						// Make sure its not already in use
						$exists = App::getEntityRepository('DeskPRO:PersonEmail')->getEmail($this->newticket->person->email);
						if ($exists) {
							$this->addError('person.email.exists');
						}
					}
				}
			}
		}

		#------------------------------
		# All other fields
		#------------------------------

		if ($this->display_fields) {
			$this->_traverseItems($this->display_fields);
		}

		#------------------------------
		# Return results
		#------------------------------

		if ($this->errors) {
			return false;
		}

		return true;
	}

	protected function _traverseItems($layout)
	{
		foreach ($layout as $item) {
			$this->_validateItem($item);
		}
	}

	protected function _validateItem(LayoutField $item)
	{
		if ($item->hasCriteria() && !$item->getCriteria()->isTicketMatch($this->mock_ticket)) {
			return;
		}

		switch ($item->getFieldType()) {
			case 'cc_emails':
				if ($this->newticket->ticket->cc_emails) {
					$cc_emails = explode(',', $this->newticket->ticket->cc_emails);
					foreach ($cc_emails as $cc) {
						$cc = strtolower(trim($cc));
						if (!\Orb\Validator\StringEmail::isValueValid($cc)) {
							$this->addError('ticket.cc_emails.invalid');
							break;
						}
					}
				}
				break;

			case 'user_name':
				if ($this->newticket->person) {
					$validator = new \Orb\Validator\StringLength(array('min' => 2));
					if (!$validator->isValid($this->newticket->person->name)) {
						$this->addError('person.name.short');
					}
				}
				break;

			case 'product':
				if (App::getSetting('core.use_product')) {
					$validator = new \Application\DeskPRO\Validator\GenericCategory(array(
						'category_repository' => App::getEntityRepository('DeskPRO:Product'),
						'allow_none' => !App::getSetting('core_tickets.field_validation_ticket_prod_user_required')
					));
					if (!$validator->isValid($this->newticket->ticket->product_id)) {
						$this->addError('ticket.product_id.invalid');
					}
				}
				break;

			case 'category':
				if (App::getSetting('core.use_ticket_category')) {
					$validator = new \Application\DeskPRO\Validator\GenericCategory(array(
						'category_repository' => App::getEntityRepository('DeskPRO:TicketCategory'),
						'allow_none' => !App::getSetting('core_tickets.field_validation_ticket_cat_user_required')
					));
					if (!$validator->isValid($this->newticket->ticket->category_id)) {
						$this->addError('ticket.category_id.invalid');
					}
				}
				break;

			case 'priority':
				if (App::getSetting('core.use_ticket_priority')) {
					$validator = new \Application\DeskPRO\Validator\TicketPriority(array(
						'allow_none' => !App::getSetting('core_tickets.field_validation_ticket_pri_user_required')
					));
					if (!$validator->isValid($this->newticket->ticket->priority_id)) {
						$this->addError('ticket.priority_id.invalid');
					}
				}
				break;

			case 'ticket_field':
				$field = App::getSystemService('TicketFieldsManager')->getFieldFromId($item->getFieldId());
				if ($field && $field->is_enabled) {
					$errors = $field->getHandler()->validateFormData($this->newticket->custom_ticket_fields);
					foreach ($errors as $code) {
						$this->addError('ticket.' . $code);
					}
				}
				break;

			case 'user_field':
				$field = App::getSystemService('PersonFieldsManager')->getFieldFromId($item->getFieldId());
				if ($field && $field->is_enabled) {
					$errors = $field->getHandler()->validateFormData($this->newticket->custom_user_fields);
					foreach ($errors as $code) {
						$this->addError('person.' . $code);
					}
				}
				break;

			case 'captcha':
				if (!$this->captca) {
					break;
				}

				if (!$this->captca->validate()) {
					$this->addError('captcha.invalid');
				}
		}
	}
}
