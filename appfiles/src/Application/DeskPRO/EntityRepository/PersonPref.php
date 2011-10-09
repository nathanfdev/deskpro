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

use Application\DeskPRO\App;

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
			WHERE person_id = ? AND name LIKE ?
		", array($person_id, "$pref_group.%"));

		$ret_prefs = array();

		while ($pref = $statement->fetch(\PDO::FETCH_ASSOC)) {
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

	public function getForPerson($pref_name, $person)
	{
		try {
			return $this->getEntityManager()->createQuery("
				SELECT p
				FROM DeskPRO:PersonPref p
				WHERE p.name = ?1 AND p.person = ?2
			")->setParameter(1, $pref_name)->setParameter(2, $person)->getSingleResult();
		} catch (\Exception $e) {
			return null;
		}
	}


	/**
	 * Get the value of a specific setting.
	 *
	 * @param string|array $pref_name A pref name or array of names
	 * @param int $person_id
	 * @return mixed
	 */
	public function getPrefForPersonId($pref_name, $person_id)
	{
		if (is_array($pref_name)) {
			$is_single = false;
			$args = array($person_id);
			$args = array_merge($args, $pref_name);

			$in_str = implode(',', array_fill(0, count($pref_name), '?'));

			$prefs = App::getDb()->fetchAllKeyed("
				SELECT name, value_str, value_array
				FROM people_prefs
				WHERE person_id = ? AND name IN ($in_str)
			", $args, 'name');

			if (!$prefs) {
				return array();
			}
		} else {
			$is_single = true;
			$pref = $this->getEntityManager()->getConnection()->fetchAssoc("
				SELECT value_str, value_array
				FROM people_prefs
				WHERE person_id = ? AND name = ?
			", array($person_id, $pref_name));

			if (!$pref) {
				return null;
			}

			$prefs = array($pref_name => $pref);
		}

		$ret = array();
		foreach ($prefs as $pref_name => $pref) {
			if ($pref['value_array']) {
				$pref['value_array'] = @unserialize($pref['value_array']);
			}

			$pref = is_array($pref['value_array']) ? $pref['value_array'] : $pref['value_str'];

			$ret[$pref_name] = $pref;
		}

		if ($is_single) {
			return array_pop($ret);
		} else {
			return $ret;
		}
	}

	/**
	 * @param $pref_name
	 * @param $person_id
	 * @return void
	 */
	public function deletePrefForPersonId($pref_name, $person_id)
	{
		$pref = $this->getEntityManager()->getConnection()->executeUpdate("
			DELETE FROM people_prefs
			WHERE person_id = ? AND name = ? LIMIT 1
		", array($person_id, $pref_name));
	}
}
