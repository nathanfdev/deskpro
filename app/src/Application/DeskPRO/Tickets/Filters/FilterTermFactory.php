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

namespace Application\DeskPRO\Tickets\Filters;

class FilterTermFactory
{
	public function createFromArray(array $term_info)
	{
		if (empty($term_info['type'])) {
			throw new \InvalidArgumentException("Missing type");
		}

		if (empty($term_info['op'])) {
			$term_info['op'] = 'is';
		}

		if (empty($term_info['options'])) {
			$term_info['options'] = array();
		}

		return $this->create($term_info['type'], $term_info['op'], $term_info['options']);
	}

	public function create($type, $op, array $options)
	{
		if (preg_match('#^Filter(User|Ticket|Org)Field(\d+)$#', $type, $m)) {
			$class_type = 'Filter' . $m[1] . 'Field';
			$options['field_id'] = $m[2];
		} else {
			$class_type = $type;
		}

		$class_name = "Application\\DeskPRO\\Tickets\\Filters\\Terms\\$class_type";
		if (!class_exists($class_name)) {
			throw new \InvalidArgumentException("Unknown term $type (could not locate class: $class_name)");
		}

		$term = new $class_name($op, $options);
		return $term;
	}
}