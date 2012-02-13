<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\CustomFields;

use Application\DeskPRO\App;
use Orb\Util\Util;

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
			$old_value = $handler->renderText($all_display_data[$field_def->id]['value'], $all_display_data[$field_def->id]);

			if ($old_value) {
				$old_value = trim(str_replace(array("\n", "\r\n"), ' ', strip_tags($old_value)));
			}
		}

		$return = parent::setCustomDataOnObject($ticket, $field_def, $in_data);

		$new_value = null;
		if ($return) {
			$all_display_data = $this->getDisplayArrayForObject($ticket);
			$handler = $all_display_data[$field_def->id]['handler'];
			$new_value = $handler->renderText($all_display_data[$field_def->id]['value'], $all_display_data[$field_def->id]);
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
