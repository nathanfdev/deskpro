<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;

/**
 * Performs groupings for specific tickets only.
 */
class SimpleGroupingCounter extends GroupingCounter
{
	protected $_ticket_ids = array();

	public function __construct(array $ticket_ids, $group_by)
	{
		$this->_ticket_ids = $ticket_ids;
		$this->setGrouping($group_by);
	}

	public function getCounts()
	{
		$group_by = 'GROUP BY field1';

		$select_fields[] = 'tickets.' . $this->grouping1 . ' AS field1';
		if ($this->grouping2) {
			$select_fields[] = 'tickets.' . $this->grouping2 . ' AS field2';
			$group_by .= ', field2';
		}
		$select_fields[] = 'COUNT(*) AS total';

		$params = array();
		$wheres = array(
			'tickets.id IN (' . implode(',', $this->_ticket_ids) . ')'
		);

		$sql = "
			SELECT " . implode(', ', $select_fields) . "
			FROM tickets
			WHERE " . implode(' AND ', $wheres) . "
			$group_by WITH ROLLUP
		";

		$db = App::getDb();

		$counts = $db->fetchAll($sql, $params);

		return $counts;
	}
}
