<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class PersonPref extends EntityRepository
{
	/**
	 * Fetch all preferences in a related group. A group is defiend by some common
	 * prefix and a dot. For example: some.group.mysetting, some.group.myothersetting
	 *
	 * Supply some.group to get an array of mysetting and myothersetting.
	 *
	 * @param string $pref_group The pref group
	 * @param int $person_id
	 * @param bool $trim_group_prefix Trim off the group prefix in the array keys
	 * @return array
	 */
	public function getPrefgroupForPersonId($pref_group, $person_id, $trim_group_prefix = true)
	{
		$pref_group = rtrim($pref_group, '.'); // incase it was supplied with dot
		$pref_group_len = strlen($pref_group) + 1; // used with trimming below

		$statement = $this->getEntityManager()->getConnection()->executeQuery("
			SELECT name, value_str, value_array
			FROM people_prefs
			WHERE person_id = ? AND name = ?
		", array($person_id, "$pref_group.%"));

		$ret_prefs = array();

		while ($pref = $statement->fetch(PDO::FETCH_ASSOC)) {
			$pref_name = $pref['name'];
			if ($trim_group_prefix) {
				$pref_name = substr($pref_name, $preg_group_len);
			}

			if ($pref['value_array']) {
				$pref['value_array'] = @unserialize($pref['value_array']);
			}

			$ret_prefs[$pref_name] = is_array($pref['value_array']) ? $pref['value_array'] : $pref['value_str'];
		}

		return $ret_prefs;
	}


	
	/**
	 * Get the value of a specific setting.
	 *
	 * @param string $pref_name
	 * @param int $person_id
	 * @return mixed
	 */
	public function getPrefForPersonId($pref_name, $person_id)
	{
		$pref = $this->getEntityManager()->getConnection()->fetchAssoc("
			SELECT value_str, value_array
			FROM people_prefs
			WHERE person_id = ? AND name = ?
		", array($person_id, $pref_name));

		if (!$pref) {
			return null;
		}

		if ($pref['value_array']) {
			$pref['value_array'] = @unserialize($pref['value_array']);
		}

		return is_array($pref['value_array']) ? $pref['value_array'] : $pref['value_str'];
	}
}