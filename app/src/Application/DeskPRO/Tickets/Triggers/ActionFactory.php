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
 */

namespace Application\DeskPRO\Tickets\Triggers;

class ActionFactory
{
	public function createFromArray(array $action_info)
	{
		if (isset($action_info['type_class'])) {
			return $this->create("@{$action_info['type_class']}", $action_info['options']);
		} else {
			return $this->create($action_info['type'], $action_info['options']);
		}
	}

	public function create($type, array $options)
	{
		if ($type[0] == '@') {
			$class_name = substr($type, 1);
		} else {
			if (preg_match('#^Set(User|Ticket|Org)Field(\d+)$#', $type, $m)) {
				$class_type = 'Set' . $m[1] . 'Field';
				$options['field_id'] = $m[2];
			} else {
				$class_type = $type;
			}
			$class_name = "Application\\DeskPRO\\Tickets\\Actions\\$class_type";
		}

		if (!class_exists($class_name)) {
			throw new \InvalidArgumentException("Unknown action $type (could not locate class: $class_name)");
		}

		$term = new $class_name($options);
		return $term;
	}
}