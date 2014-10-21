<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

class TermFactory
{
	public function createFromArray(array $term_info)
	{
		return $this->create($term_info['type'], $term_info['op'], $term_info['options']);
	}

	public function create($type, $op, array $options)
	{
		if (preg_match('#^Check(User|Ticket|Org)(Contextual)?Field(\d+)$#', $type, $m)) {
			$class_type = 'Check' . $m[1] . $m[2] . 'Field';
			$options['field_id'] = $m[3];
		} else {
			$class_type = $type;
		}

		$class_name = "Application\\DeskPRO\\Tickets\\Triggers\\Terms\\$class_type";
		if (!class_exists($class_name)) {
			throw new \InvalidArgumentException("Unknown term $type (could not locate class: $class_name)");
		}

		$term = new $class_name($op, $options);
		return $term;
	}
}