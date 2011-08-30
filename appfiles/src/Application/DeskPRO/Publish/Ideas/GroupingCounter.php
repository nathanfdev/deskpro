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

namespace Application\DeskPRO\Publish\Ideas;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

class GroupingCounter
{
	protected $grouping1 = 'status';
	protected $grouping2 = 'category_id';

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
			case 'category_id':
				$group2_structure = App::getEntityRepository('DeskPRO:IdeaCategory')->getCategoriesInHierarchy();
				break;

			default:
				foreach ($titles1 as $id => $t) {
					$group1_structure[$id] = array('title' => $t);
				}
				break;
		}

		if ($this->grouping2) {
			switch ($this->grouping2) {
				case 'category_id':
					$group2_structure = App::getEntityRepository('DeskPRO:IdeaCategory')->getCategoriesInHierarchy();
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

		$grouping1 = $this->grouping1;
		$grouping2 = $this->grouping2;

		if ($grouping1 == 'status') {
			$grouping1 = "IF(ideas.status_category_id, CONCAT(ideas.status, '.', ideas.status_category_id), ideas.status)";
		} else {
			$grouping1 = "ideas.$grouping1";
		}

		if ($grouping2 == 'status') {
			$grouping2 = "IF(ideas.status_category_id, CONCAT(ideas.status, '.', ideas.status_category_id), ideas.status)";
		} else {
			$grouping2 = "ideas.$grouping2";
		}

		$select_fields[] = "COALESCE($grouping1, 0) AS field1";
		if ($this->grouping2) {
			$select_fields[] = "COALESCE($grouping2, 0) AS field2";
			$group_by .= ', field2';
		}
		$select_fields[] = 'COUNT(*) AS total';

		$sql = "
			SELECT " . implode(', ', $select_fields) . "
			FROM ideas
			WHERE (ideas.hidden_status IS NULL OR ideas.hidden_status != 'validating')
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
			case 'category_id':
				$titles = App::getOrm()->getRepository('DeskPRO:IdeaCategory')->getCategoryNames();
				Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('core.none'));
				break;

			case 'status':

				$titles = array(
					'new' => 'New',
					'active' => 'Active',
					'closed' => 'Closed',
					'hidden' => 'Hidden',
				);

				$active_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getActiveCategories();
				$closed_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getClosedCategories();

				foreach ($active_status_cats as $cat) {
					$titles['active.' . $cat['id']] = $cat['title'];
				}
					foreach ($closed_status_cats as $cat) {
					$titles['closed.' . $cat['id']] = $cat['title'];
				}

				return $titles;

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
	 * Set the grouping fields.
	 *
	 * @param string $grouping1
	 * @param string $grouping2
	 */
	public function setGrouping($grouping1, $grouping2 = null)
	{
		$this->grouping1 = $grouping1 ? $grouping1 : 'category';
		$this->grouping2 = $grouping2 ? $grouping2 : 'status';
	}
}
