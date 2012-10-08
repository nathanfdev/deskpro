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
 * @subpackage
 */

namespace Application\DeskPRO\CustomFields;

use Application\DeskPRO\App;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Doctrine\ORM\EntityManager;

class TicketFieldManager extends FieldManager
{
	public function setCustomDataOnObject($ticket, CustomDefAbstract $field_def, array $in_data)
	{
		if (!$ticket->getTicketLogger()) {
			return parent::setCustomDataOnObject($ticket, $field_def, $in_data);
		}

		$all_display_data = $this->_orig_display;

		$old_value = null;

		if (isset($all_display_data[$field_def->id])) {
			$handler = $all_display_data[$field_def->id]['handler'];
			$old_value = $handler->renderText($all_display_data[$field_def->id]['value']);

			if ($old_value) {
				$old_value = trim(str_replace(array("\n", "\r\n"), ' ', strip_tags($old_value)));
			}
		}

		$return = parent::setCustomDataOnObject($ticket, $field_def, $in_data);

		$new_value = null;
		if ($return) {
			$all_display_data = $this->getDisplayArrayForObject($ticket);
			$handler = $all_display_data[$field_def->id]['handler'];
			$new_value = $handler->renderText($all_display_data[$field_def->id]['value']);
			if ($new_value) {
				$new_value = trim(str_replace(array("\n", "\r\n"), ' ', strip_tags($new_value)));
			}
		}

		if (($new_value || $old_value) && ($new_value != $old_value)) {
			$ticket->getTicketLogger()->recordMultiPropertyChanged(
				'custom_data',
				array('field_def' => $field_def, 'value' => $old_value),
				array('field_def' => $field_def, 'value' => $new_value)
			);
		}

		return $new_value;
	}
}
