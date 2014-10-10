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
 * @category Entities
 */

namespace Application\DeskPRO\TicketLayout;

use Application\DeskPRO\CustomFields\PersonFieldManager;
use Application\DeskPRO\CustomFields\TicketFieldManager;

class LayoutFieldFilter
{
	/**
	 * @var \Application\DeskPRO\CustomFields\TicketFieldManager
	 */
	private $ticket_fields;

	/**
	 * @var \Application\DeskPRO\CustomFields\PersonFieldManager
	 */
	private $user_fields;


	/**
	 * @param TicketFieldManager $ticket_fields
	 * @param PersonFieldManager $user_fields
	 */
	public function __construct(TicketFieldManager $ticket_fields, PersonFieldManager $user_fields)
	{
		$this->ticket_fields = $ticket_fields;
		$this->user_fields = $user_fields;
	}


	/**
	 * @param LayoutField $field
	 * @return bool
	 */
	public function isFieldValid(LayoutField $field)
	{
		switch ($field->getFieldType()) {
			case 'ticket_field':
				if (!$this->ticket_fields->getFieldFromId($field->getFieldId())) {
					return false;
				}
				break;

			case 'user_field':
				if (!$this->user_fields->getFieldFromId($field->getFieldId())) {
					return false;
				}
				break;

			case 'category':
				return $this->ticket_fields->isCategoryEnabled();
			case 'priority':
				return $this->ticket_fields->isPriorityEnabled();
			case 'workflow':
				return $this->ticket_fields->isWorkflowEnabled();
			case 'product':
				return $this->ticket_fields->isProductEnabled();
		}

		return true;
	}


	/**
	 * @param Layout $layout
	 * @return array
	 */
	public function getInvalidIds(Layout $layout)
	{
		$invalid = array();
		foreach ($layout as $f) {
			if (!$this->isFieldValid($f)) {
				$invalid[] = $f->getId();
			}
		}

		return $invalid;
	}


	/**
	 * @param Layout $layout
	 * @return Layout
	 */
	public function filterInvalid(Layout $layout)
	{
		foreach ($this->getInvalidIds($layout) as $id) {
			$layout->remove($id);
		}

		return $layout;
	}
}