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
	const MODE_AGENT         = 'agent';
	const MODE_AGENT_TEAM    = 'agent_team';
	const MODE_PARTICIPANT   = 'participant';
	const MODE_UNASSIGNED    = 'unassigned';
	const MODE_ALL           = 'all';

	protected $grouping1 = 'department_id';
	protected $grouping2 = null;

	protected $mode = 'unassigned';
	protected $this_person_id = null;

	protected $terms = array();

	/**
	 * Get an array of counts suitable for looping in a template etc
	 *
	 * @return array
	 */
	public function getDisplayArray()
	{
		#------------------------------
		# Connect counts to titles
		#------------------------------

		$display_elements = $this->getDisplayElementsArray();
		$titles1 = $display_elements['titles1'];
		$titles2 = $display_elements['titles2'];
		$counts  = $display_elements['counts'];

		Arrays::unshiftAssoc($titles1, -1, 'TOTAL');
		if ($titles2) {
			Arrays::unshiftAssoc($titles2, -1, 'TOTAL');
		}

		$items = array();

		$group1_has = array();
		$group2_has = array();

		foreach ($titles1 as $field1_id => $field1_title) {

			if (!isset($counts[$field1_id])) continue;

			$countinfo = $counts[$field1_id];

			$group1_has[] = $field1_id;

			$row = array();
			$row['id'] = $field1_id;
			$row['title'] = $field1_title;
			$row['total'] = $countinfo['total'];

			if (!empty($countinfo['sub'])) {

				$row['sub'] = array();
				foreach ($titles2 as $field2_id => $field2_title) {

					if (!isset($countinfo['sub'][$field2_id])) continue;
					$countinfo2 = $countinfo['sub'][$field2_id];

					$group2_has[] = $field2_id;

					$row2 = array();
					$row2['id'] = $field2_id;
					$row2['title'] = $field2_title;
					$row2['total'] = $countinfo2['total'];

					$row['sub'][$field2_id] = $row2;
				}
			}

			$items[$field1_id] = $row;
		}

		$group1_has = array_unique($group1_has);
		$group2_has = array_unique($group2_has);

		#------------------------------
		# Now fetch hierarchy which might be used
		#------------------------------

		$group1_structure = array();
		$group2_structure = array();

		switch ($this->grouping1) {
			case 'department_id':
				$group1_structure = App::getEntityRepository('DeskPRO:Department')->getDepartmentsInHierarchy();
				break;

			case 'category_id':
				$group1_structure = App::getEntityRepository('DeskPRO:TicketCategory')->getCategoriesInHierarchy();
				break;

			case 'product_id':
				$group1_structure = App::getEntityRepository('DeskPRO:Product')->getCategoriesInHierarchy();
				break;

			default:
				foreach ($titles1 as $id => $t) {
					$group1_structure[$id] = array('title' => $t);
				}
				break;
		}

		if ($this->grouping2) {
			switch ($this->grouping2) {
				case 'department_id':
					$group2_structure = App::getEntityRepository('DeskPRO:Department')->getDepartmentsInHierarchy();
					break;

				case 'category_id':
					$group2_structure = App::getEntityRepository('DeskPRO:TicketCategory')->getCategoriesInHierarchy();
					break;

				case 'product_id':
					$group2_structure = App::getEntityRepository('DeskPRO:Product')->getCategoriesInHierarchy();
					break;

				default:
					foreach ($titles2 as $id => $t) {
						$group2_structure[$id] = array('title' => $t);
					}
					break;
			}
		}

		return array(
			'items' => $items,
			'group1_structure' => $group1_structure,
			'group2_structure' => $group2_structure,
		);
	}

	/**
	 * Sort a display array so that the biggest counts are first
	 *
	 * @param array $display_array
	 */
	public function sortDisplayArray(array &$display_array)
	{
		uasort($display_array, array($this, '_sortDisplayArrayCallback'));
	}

	public function _sortDisplayArrayCallback($a, $b)
	{
		if ($a['total'] == $b['total']) {
			return 0;
		}

		return ($a['total'] < $b['total']) ? -1 : 1;
	}




	/**
	 * Get the raw counts
	 *
	 * @return array
	 */
	public function getCounts()
	{
		$group_by = 'GROUP BY field1';

		$select_fields[] = "COALESCE(tickets.{$this->grouping1}, 0) AS field1";
		if ($this->grouping2) {
			$select_fields[] = "COALESCE(tickets.{$this->grouping2}, 0) AS field2";
			$group_by .= ', field2';
		}
		$select_fields[] = 'COUNT(*) AS total';

		$wheres = array('tickets.status = \'open\'');

		// Standard agent perms
		$agent = App::getEntityRepository('DeskPRO:Person')->find($this->this_person_id);
		$agent->loadHelper('AgentPermissions');
		$agent->loadHelper('AgentTeam');

		// perms only matter if person has permissions applied at all
		if ($agent->getDisallowedDepartments()) {

			$where_perm = array();
			$where_perm[] = "tickets.agent_id = {$agent['id']}";

			if ($agent->getAgentTeamIds()) {
				$where_perm[] = "tickets.agent_team_id IN (" . implode(',', $agent->getAgentTeamIds()) . ")";
			}

			$where_perm[] = "tickets.department_id IN (" . implode(',', $agent->getAllowedDepartments()) . ")";
			$where_perm[] = "part_check.person_id = {$agent['id']}";

			$where_perm = implode(' OR ', $where_perm);

			$where[] = "($where_perm)";
		}

		switch ($this->mode) {
			case self::MODE_AGENT:
				$wheres[] = 'tickets.agent_id = ' . $agent['id'];
				break;

			case self::MODE_AGENT_TEAM:
				$wheres[] = "tickets.agent_team_id IN (" . implode(',', $agent->getAgentTeamIds()) . ")";
				break;

			case self::MODE_PARTICIPANT:
				$wheres[] = "tickets.agent_team_id IN (" . implode(',', $agent->getAgentTeamIds()) . ")";
				break;

			case self::MODE_ALL:
				break;

			case self::MODE_UNASSIGNED:
				$wheres[] = 'tickets.agent_id IS NULL';
				break;
		}

		//SELECT tickets.department_id, tickets.priority_id, COUNT(*) as cnt FROM tickets GROUP BY tickets.department_id, tickets.priority_id WITH ROLLUP
		// TODO this should be using active table
		$sql = "
			SELECT " . implode(', ', $select_fields) . "
			FROM tickets
			LEFT JOIN tickets_participants ON (tickets_participants.ticket_id = tickets.id)
			LEFT JOIN tickets_participants AS part_check ON (part_check.ticket_id = tickets.id)
			WHERE " . implode(' AND ', $wheres) . "
			$group_by WITH ROLLUP
		";

		$db = App::getDb();

		$counts = $db->fetchAll($sql);

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
				$titles = App::getOrm()->getRepository('DeskPRO:Department')->getDepartmentNames();
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('core.none'));
				break;

			case 'product_id':
				$titles = App::getOrm()->getRepository('DeskPRO:Product')->getProductNames();
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('core.none'));
				break;

			case 'category_id':
				$titles = App::getOrm()->getRepository('DeskPRO:TicketCategory')->getCategoryNames();
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('core.none'));
				break;

			case 'agent_id':
				$titles = App::getOrm()->getRepository('DeskPRO:Person')->getAgentNames();
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('core.unassigned'));
				break;

			case 'workflow_id':
				$titles = App::getOrm()->getRepository('DeskPRO:Workflow')->getWorkflowNames();
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('core.none'));
				break;

			case 'priority_id':
				$titles = App::getOrm()->getRepository('DeskPRO:TicketPriority')->getPriorityNames();
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('core.none'));
				break;

			default:
				// Just make all titles the ids themselves by default,
				// useful for things like status which might be rendered into words after
				if ($ids) {
					$titles = array_combine($ids, $ids);
				} else {
					$titles = array();
				}
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

		$this->this_person_id = $opt;
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