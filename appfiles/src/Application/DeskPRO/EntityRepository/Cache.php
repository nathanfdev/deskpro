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

class Cache extends EntityRepository
{
	public function load($id)
	{
		return false;
		$data = App::getDb()->fetchColumn("SELECT data FROM cache WHERE id = ?", array($id));

		if (!$data) {
			return false;
		}

		$data = @unserialize($data);

		if (isset($data['VALUE'])) {
			return $data['VALUE'];
		}

		return $data;
	}

	public function save($id, $data, $lifetime = null)
	{
		if (!is_array($data)) {
			$data = array('VALUE' => $data);
		}

		$data = serialize($data);

		$expire = null;
		if ($lifetime) {
			$expire = date('Y-m-d H:i:s', time()+$lifetime);
		}

		App::getDb()->executeUpdate(
			"REPLACE INTO cache SET id = ?, data = ?, date_expire = ?", array(
			$id, $data, $expire
		));

		return true;
	}

	public function delete($id)
	{
		return App::getDb()->executeUpdate("DELETE FROM cache WHERE id LIKE ?", array($id . '%'));
	}

	/**
	 * Clean up all expired cache entries
	 */
	public function cleanExpired()
	{
		return App::getDb()->executeUpdate("DELETE FROM cache WHERE date_expire < ?", array(date('Y-m-d H:m:s')));
	}
}