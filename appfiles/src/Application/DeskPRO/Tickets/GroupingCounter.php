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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

class GroupingCounter
{
	const MODE_YOUR       = 'your';
	const MODE_UNASSIGNED = 'unassigned';
	const MODE_OTHERS     = 'others';

	protected $grouping1 = 'department_id';
	protected $grouping2 = null;

	protected $mode = 'unassigned';
	protected $this_person_id = null;


	
	/**
	 * Get an array of counts suitable for looping in a template etc
	 * 
	 * @return array
	 */
	public function getDisplayArray()
	{
		$display_elements = $this->getDisplayElementsArray();
		$titles1 = $display_elements['titles1'];
		$titles2 = $display_elements['titles2'];
		$counts  = $display_elements['counts'];

		Arrays::unshiftAssoc($titles1, -1, 'TOTAL');
		if ($titles2) {
			Arrays::unshiftAssoc($titles2, -1, 'TOTAL');
		}

		$return = array();
		
		foreach ($titles1 as $field1_id => $field1_title) {

			if (!isset($counts[$field1_id])) continue;

			$countinfo = $counts[$field1_id];
			
			$row = array();
			$row['id'] = $field1_id;
			$row['title'] = $field1_title;
			$row['total'] = $countinfo['total'];

			if (!empty($countinfo['sub'])) {

				$row['sub'] = array();
				foreach ($titles2 as $field2_id => $field2_title) {

					if (!isset($countinfo['sub'][$field2_id])) continue;
					$countinfo2 = $countinfo['sub'][$field2_id];

					$row2 = array();
					$row2['id'] = $field2_id;
					$row2['title'] = $field2_title;
					$row2['total'] = $countinfo2['total'];

					$row['sub'][] = $row2;
				}
			}

			$return[] = $row;
		}

		return $return;
	}



	
	/**
	 * Get the raw counts
	 * 
	 * @return array
	 */
	public function getCounts()
	{
		$group_by = 'GROUP BY field1';

		$select_fields[] = 'tickets.' . $this->grouping1 . ' AS field1';
		if ($this->grouping2) {
			$select_fields[] = 'tickets.' . $this->grouping2 . ' AS field2';
			$group_by .= ', field2';
		}
		$select_fields[] = 'COUNT(*) AS total';


		$wheres = array('tickets.status = ?');
		$params = array('open');

		switch ($this->mode) {
			case self::MODE_YOUR:
				$wheres[] = 'tickets.agent_id = ?';
				$params[] = $this->this_person_id;
				break;

			case self::MODE_OTHERS:
				$wheres[] = 'tickets.agent_id != ?';
				$params[] = $this->this_person_id;
				break;

			case self::MODE_UNASSIGNED:
				$wheres[] = 'tickets.agent_id IS NULL';
				break;
		}

		//SELECT tickets.department_id, tickets.priority_id, COUNT(*) as cnt FROM tickets GROUP BY tickets.department_id, tickets.priority_id WITH ROLLUP

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

	

	/**
	 * Get information about strucutred counts and titles.
	 * 
	 * @return array
	 */
	public function getDisplayElementsArray()
	{
		$counts = $this->getCounts();

		#------------------------------
		# Get titles for each grouping, and sort into a keyed structure
		#------------------------------

		$ids1 = array();
		if ($this->grouping2) {
			$ids2 = array();
		}

		// $counts_structure becomes:
		// array(field1 => array(total => xxx, sub => array(someid => 123, someid2 => 123 ...) )

		$counts_structured = array();
		foreach ($counts as $count) {

			// Store ID's
			if ($count['field1'] !== null) {
				$ids1[] = $count['field1'];
			}

			if ($this->grouping2 AND $count['field2'] !== null) {
				$ids2[] = $count['field2'];
			}

			#------------------------------
			# Into structure
			#------------------------------

			// Set ROLLUP's (totals) to -1
			if ($count['field1'] === null) $count['field1'] = -1;
			if ($this->grouping2 AND $count['field2'] === null) $count['field2'] = -1;

			// Init array keys
			if (!isset($counts_structured[$count['field1']])) {
				$counts_structured[$count['field1']] = array('total' => $count['total']);
				if ($this->grouping2) {
					$counts_structured[$count['field1']]['sub'] = array();
				}
			}

			// Save numbers
			if ($this->grouping2) {
				if ($count['field2'] == -1) {
					$counts_structured[$count['field1']]['total'] = $count['total'];
				} else {
					$counts_structured[$count['field1']]['sub'][$count['field2']] = $count['total'];
				}
			}
		}

		$ids1 = array_unique($ids1);

		if ($this->grouping2) {
			$ids2 = array_unique($ids2);
		}

		$titles1 = $this->getFieldTitles($this->grouping1, $ids1);
		$titles2 = null;
		if ($this->grouping2) {
			$titles2 = $this->getFieldTitles($this->grouping2, $ids2);
		}

		return array(
			'titles1' => $titles1,
			'titles2' => $titles2,
			'counts'  => $counts_structured
		);
	}


	
	/**
	 * Get a string of id=>title for a particular field, given IDs.
	 * Sometimes $ids is not needed (ie departments can all be fetched),
	 * other times it's important (ie dont want every company name in the entire db).
	 *
	 * @param string $field
	 * @param array $ids
	 * @return array
	 */
	public function getFieldTitles($field, array $ids = null)
	{
		$titles = null;
		switch ($field) {
			case 'department_id':
				$titles = App::getOrm()->getRepository('DeskPRO:Department')->getFlatDepartmentNames();
				break;
			
			case 'category_id':
				$titles = App::getOrm()->getRepository('DeskPRO:TicketCategory')->getAllCategoryNames();
				break;
			
			case 'priority_id':
				$titles = App::getOrm()->getRepository('DeskPRO:TicketPriority')->getPriorityNames();
				break;
				
			default:
				// Just make all titles the ids themselves by default,
				// useful for things like status which might be rendered into words after
				$titles = array_combine($ids, $ids);
				break;
		}

		return $titles;
	}



	/**
	 * Set mode which defines which kinds of tickets we want.
	 *
	 * When $mode is MODE_YOUR or MODE_OTHERS, $opt sholud be a person ID.
	 *
	 * @param string $mode
	 * @param mixed $opt
	 */
	public function setMode($mode, $opt = null)
	{
		$this->mode = $mode;

		if ($mode == self::MODE_YOUR OR $mode == self::MODE_OTHERS) {
			if ($opt === null) $opt = App::getCurrentPerson()->getId();
			$this->this_person_id = $opt;
		}
	}


	
	/**
	 * Set the grouping fields.
	 *
	 * @param string $grouping1
	 * @param string $grouping2
	 */
	public function setGrouping($grouping1, $grouping2 = null)
	{
		$this->grouping1 = $grouping1 ? $grouping1 : 'department';
		$this->grouping2 = $grouping2 ? $grouping2 : null;
	}
}