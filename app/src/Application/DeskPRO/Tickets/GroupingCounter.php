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
 * @subpackage Tickets
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Searcher\TicketSearch;

use Orb\Util\Arrays;
use Orb\Util\Util;

class GroupingCounter
{
	const MODE_AGENT         = 'agent';
	const MODE_AGENT_TEAM    = 'agent_team';
	const MODE_PARTICIPANT   = 'participant';
	const MODE_UNASSIGNED    = 'unassigned';
	const MODE_ALL           = 'all';
	const MODE_SPECIFY       = 'specify';

	protected $grouping1 = 'department';
	protected $grouping2 = null;

	protected $mode = 'unassigned';
	protected $tickets = array();
	protected $this_person = null;

	protected $terms = array();

	protected $grouping_summary = '';

	public function getGroupingSummary()
	{
		return $this->grouping_summary;
	}

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

		foreach ($titles1 as $field1 => $field1_title) {

			if (!isset($counts[$field1])) continue;

			$countinfo = $counts[$field1];

			$group1_has[] = $field1;

			$row = array();
			$row['id'] = $field1;
			$row['title'] = $field1_title;
			$row['total'] = $countinfo['total'];

			if (!empty($countinfo['sub'])) {

				$row['sub'] = array();
				foreach ($titles2 as $field2 => $field2_title) {

					if (!isset($countinfo['sub'][$field2])) continue;
					$countinfo2 = $countinfo['sub'][$field2];

					$group2_has[] = $field2;

					$row2 = array();
					$row2['id'] = $field2;
					$row2['title'] = $field2_title;
					$row2['total'] = $countinfo2['total'];

					$row['sub'][$field2] = $row2;
				}
			}

			$items[$field1] = $row;
		}

		$group1_has = array_unique($group1_has);
		$group2_has = array_unique($group2_has);

		#------------------------------
		# Now fetch hierarchy which might be used
		#------------------------------

		$group1_structure = array();
		$group2_structure = array();

		$group1_structure = $this->getFieldStructure($this->grouping1, $titles1, $display_elements['ids1']);

		if ($this->grouping2) {
			$group2_structure = $this->getFieldStructure($this->grouping2, $titles2, $display_elements['ids2']);
		}

		return array(
			'items' => $items,
			'counts' => $counts,
			'titles1' => $titles1,
			'titles2' => $titles2,
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

	protected function buildSelectTerm($grouping, $field)
	{
		if ($this->isTimeField($grouping)) {
			return $this->makeTimeFieldSelect($grouping, $field);
		} elseif ($grouping == 'language') {
			$default = App::getEntityRepository('DeskPRO:Language')->getDefault();
			return "COALESCE(tickets.language_id, {$default['id']}) AS $field";
		} else {
			try {
				$group_fieldname = \Application\DeskPRO\Searcher\TicketSearch::getTableField($grouping);
			} catch (\InvalidArgumentException $e) {
				$group_fieldname = \Application\DeskPRO\Searcher\TicketSearch::getTableField('department');
			}
			return "COALESCE(tickets.{$group_fieldname}, 0) AS $field";
		}
	}


	/**
	 * Get the raw counts
	 *
	 * @return array
	 */
	public function getCounts()
	{
		$group_by = 'GROUP BY field1';

		$select_fields[] = $this->buildSelectTerm($this->grouping1, 'field1');

		if ($this->grouping2) {
			$select_fields[] = $this->buildSelectTerm($this->grouping2, 'field2');
			$group_by .= ', field2';
		}
		$select_fields[] = 'COUNT(*) AS total';

		// Doing a search on a sys-type filter at the same time

		if ($this->mode != self::MODE_SPECIFY) {
			$wheres = array('tickets.status = \'open\'');

			// Standard agent perms
			$agent = App::getEntityRepository('DeskPRO:Person')->find($this->this_person);
			$agent->loadHelper('AgentPermissions');
			$agent->loadHelper('AgentTeam');

			// perms only matter if person has permissions applied at all
			if ($agent->getDisallowedDepartments()) {

				$where_perm = array();
				$where_perm[] = "tickets.agent = {$agent['id']}";

				if ($agent->getAgentTeamIds()) {
					$where_perm[] = "tickets.agent_team IN (" . implode(',', $agent->getAgentTeamIds()) . ")";
				}

				$where_perm[] = "tickets.department IN (" . implode(',', $agent->getAllowedDepartments()) . ")";
				$where_perm[] = "part_check.person = {$agent['id']}";

				$where_perm = implode(' OR ', $where_perm);

				$where[] = "($where_perm)";
			}

			switch ($this->mode) {
				case self::MODE_AGENT:
					$wheres[] = 'tickets.agent = ' . $agent['id'];
					break;

				case self::MODE_AGENT_TEAM:
					$wheres[] = "tickets.agent_team IN (" . implode(',', $agent->getAgentTeamIds()) . ")";
					break;

				case self::MODE_PARTICIPANT:
					$wheres[] = "tickets.agent_team IN (" . implode(',', $agent->getAgentTeamIds()) . ")";
					break;

				case self::MODE_ALL:
					break;

				case self::MODE_UNASSIGNED:
					$wheres[] = 'tickets.agent IS NULL';
					break;
			}

			$sql = "
				SELECT " . implode(', ', $select_fields) . "
				FROM tickets
				LEFT JOIN tickets_participants ON (tickets_participants.ticket = tickets.id)
				LEFT JOIN tickets_participants AS part_check ON (part_check.ticket = tickets.id)
				WHERE " . implode(' AND ', $wheres) . "
				$group_by WITH ROLLUP
			";

		// We have ticket IDs already (mode = specify)
		} else {

			if (!$this->tickets) {
				return array();
			}

			$wheres[] = "tickets.id IN (" . implode(',', $this->tickets) . ")";
			$sql = "
				SELECT " . implode(', ', $select_fields) . "
				FROM tickets
				WHERE " . implode(' AND ', $wheres) . "
				$group_by WITH ROLLUP
			";
		}

		$db = App::getDb();

		$counts = $db->fetchAll($sql);

		return $counts;
	}


	/**
	 * Check if a field is a time field
	 *
	 * @param $field
	 * @return bool
	 */
	public function isTimeField($field)
	{
		return in_array($field, array(
			TicketSearch::TERM_USER_WAITING,
			TicketSearch::TERM_TOTAL_USER_WAITING,
			TicketSearch::TERM_DATE_CREATED,
		));
	}


	/**
	 * Generates some nasty SQL to get MySQL to group on the right date range value.
	 *
	 * @param $field
	 * @param $select_name
	 * @return string
	 */
	public function makeTimeFieldSelect($field, $select_name)
	{
		$times = array_keys($this->getTimeTitles());
		$fieldname = \Application\DeskPRO\Searcher\TicketSearch::getTableField($field);
		$times = array_reverse($times);

		$now = time();

		$sql = "CASE ";

		$parts = array();
		foreach ($times as $t) {


			if ($field == TicketSearch::TERM_TOTAL_USER_WAITING) {
				// total time is stored in seconds, so we're not doing a date compare
				$date = $t;
				$parts[] = " WHEN (tickets.$fieldname + ($now - COALESCE(UNIX_TIMESTAMP(date_user_waiting)))) >= $date THEN $t ";
			} else {
				// Get a real time so we dont have mysql doing calculations,
				// and we dont need to do a subquery etc
				$date = date('Y-m-d H:i:s', $now - $t);
				$parts[] = " WHEN tickets.$fieldname <= '$date' THEN $t ";
			}
		}

		$sql .= implode('', $parts) . " ELSE 9000000000 END AS $select_name";

		return $sql;
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
		$ids2 = array();

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
			'ids1'    => $ids1,
			'ids2'    => $ids2,
			'counts'  => $counts_structured
		);
	}



	/**
	 * Get's a hierarchy array of titles for use in a template.
	 *
	 * @param $field
	 * @param array $titles
	 * @param array $ids
	 * @return array
	 */
	public function getFieldStructure($field, array $titles, array $ids)
	{
		switch ($field) {
			case TicketSearch::TERM_DEPARTMENT:
				$group_structure = App::getEntityRepository('DeskPRO:Department')->getDepartmentsInHierarchy();
				break;

			case TicketSearch::TERM_CATEGORY:
				$group_structure = App::getEntityRepository('DeskPRO:TicketCategory')->getCategoriesInHierarchy();
				break;

			case TicketSearch::TERM_PRODUCT:
				$group_structure = App::getEntityRepository('DeskPRO:Product')->getCategoriesInHierarchy();
				break;

			default:
				$group_structure = array();
				foreach ($titles as $id => $t) {
					$group_structure[$id] = array('id' => $id, 'title' => $t);
				}

				// Make note of unknown items (should never happen, but better to include than not!)
				foreach ($ids as $id) {
					if (!isset($group_structure[$id])) {
						$group_structure[$id] = array('id' => $id, 'title' => "Unknow $id");
					}
				}


				// But remove the -1 rollups
				unset($group_structure[-1]);
				break;
		}

		return $group_structure;
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
	public function getFieldTitles($field, array $ids)
	{
		$titles = null;
		switch ($field) {
			case TicketSearch::TERM_DEPARTMENT:
				$this->grouping_summary = "Department";
				$titles = App::getOrm()->getRepository('DeskPRO:Department')->getDepartmentNames();
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('agent.none'));
				break;

			case TicketSearch::TERM_AGENT:
				$this->grouping_summary = "Agent";
				$titles = App::getOrm()->getRepository('DeskPRO:Person')->getAgentNames();
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('agent.unassigned'));
				break;

			case TicketSearch::TERM_AGENT_TEAM:
				$this->grouping_summary = "Agent Team";
				$titles = App::getOrm()->getRepository('DeskPRO:AgentTeam')->getTeamNames();
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('agent.unassigned'));
				break;

			case TicketSearch::TERM_URGENCY:
				$this->grouping_summary = "Urgency";
				$titles = range(1, 10);
				break;

			case TicketSearch::TERM_CATEGORY:
				$this->grouping_summary = "Category";
				$titles = App::getOrm()->getRepository('DeskPRO:TicketCategory')->getCategoryNames();
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('agent.none'));
				break;

			case TicketSearch::TERM_PRIORITY:
				$this->grouping_summary = "Priority";
				$titles = App::getOrm()->getRepository('DeskPRO:TicketPriority')->getPriorityNames();
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('agent.none'));
				break;

			case TicketSearch::TERM_PRODUCT:
				$this->grouping_summary = "Product";
				$titles = App::getOrm()->getRepository('DeskPRO:Product')->getProductNames();
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('agent.none'));
				break;

			case TicketSearch::TERM_WORKFLOW:
				$this->grouping_summary = "Workflow";
				$titles = App::getOrm()->getRepository('DeskPRO:TicketWorkflow')->getWorkflowNames();
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('agent.none'));
				break;

			case TicketSearch::TERM_ORGANIZATION:
				$this->grouping_summary = "Organization";
				$titles = App::getOrm()->getRepository('DeskPRO:Organization')->getOrganizationNames($ids);
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('agent.none'));
				break;

			case TicketSearch::TERM_LANGUAGE:
				$this->grouping_summary = "Language";
				$titles = App::getOrm()->getRepository('DeskPRO:Language')->getTitles();
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('agent.none'));
				break;

			case TicketSearch::TERM_USER_WAITING:
				$this->grouping_summary = "Time User Waiting";
				$titles = $this->getTimeTitles();
				break;

			case TicketSearch::TERM_TOTAL_USER_WAITING:
				$this->grouping_summary = "Total Time User Waiting";
				$titles = $this->getTimeTitles();
				break;

			case TicketSearch::TERM_DATE_CREATED:
				$this->grouping_summary = "Time Since Creation";
				$titles = $this->getTimeTitles();
				break;

			default:

				$this->grouping_summary = $field;

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

	public static function getTimeTitles()
	{
		$times = array(
			300        => '< 5 minutes',
			900        => '5 - 15 minutes',
			1800       => '15 - 30 minutes',
			3600       => '30 - 60 minutes',
			7200       => '1 - 2 hours',
			10800      => '2 - 3 hours',
			14400      => '3 - 4 hours',
			21600      => '4 - 6 hours',
			43200      => '6 - 12 hours',
			86400      => '12 - 24 hours',
			172800     => '1 - 2 days',
			259200     => '2 - 3 days',
			345600     => '3 - 4 days',
			432000     => '4 - 5 days',
			518400     => '5 - 6 days',
			604800     => '6 - 7 days',
			1209600    => '1 - 2 weeks',
			1814400    => '2 - 3 weeks',
			2419200    => '3 - 4 weeks',
			4838400    => '1 - 2 months',
			7257600    => '2 - 3 months',
			9676800    => '3 - 4 months',
			12096000   => '4 - 5 months',
			14515200   => '5 - 6 months',
			9000000000 => '> 6 months'
		);

		return $times;
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

		if ($this->mode == self::MODE_SPECIFY) {
			$this->tickets = $opt;
		} else {
			$this->this_person = $opt;
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


	/**
	 * Transforms a grouping var and choice into a search term for TicketSearch
	 *
	 * @param $groupvar
	 * @param $groupchoice
	 * @return array
	 */
	public static function getSearchTerm($groupvar, $groupchoice)
	{
		switch ($groupvar) {
			case TicketSearch::TERM_USER_WAITING:
			case TicketSearch::TERM_DATE_CREATED:

				$times = array_keys(self::getTimeTitles());
				$key = array_search($groupchoice, $times);

				if ($key == 0) {
					$date = new \DateTime('-5 minutes');
					return array('type' => $groupvar, 'op' => 'lte', 'options' => array('date1' => $date));
				} elseif ($key == (count($times) - 1)) {
					$date = new \DateTime('-6 months');
					return array('type' => $groupvar, 'op' => 'gte', 'options' => array('date1' => $date));
				} else {
					$date1 = new \DateTime('-' . $times[$key] . ' seconds');
					$date2 = new \DateTime('-' . $times[$key+1] . ' seconds');

					return array('type' => $groupvar, 'op' => 'between', 'options' => array('date1' => $date1, 'date2' => $date2));
				}

				break;

			case TicketSearch::TERM_TOTAL_USER_WAITING:

				$times = array_keys(self::getTimeTitles());
				$key = array_search($groupchoice, $times);

				if ($key == 0) {
					$term = array('type' => $groupvar, 'op' => 'lte', 'options' => 300);
				} elseif ($key == (count($times) - 1)) {
					$term = array('type' => $groupvar, 'op' => 'gte', 'options' => 14515200);
				} else {
					$term = array('type' => $groupvar, 'op' => 'lte', 'options' => array($times[$key], $times[$key+1]));
				}

				return $term;

			default;
				return array('type' => $groupvar, 'op' => 'is', 'options' => array($groupchoice));
		}
	}
}
