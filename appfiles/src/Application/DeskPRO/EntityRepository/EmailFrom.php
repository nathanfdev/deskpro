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

use \Orb\Util\Strings;

use \Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;

class EmailFrom extends EntityRepository
{
	protected $_froms = null;
	protected $_from_names = null;

	protected function _loadFroms()
	{
		if ($this->_froms !== null) return;

		$this->_froms = $this->findAll();
	}

	public function getFromNames()
	{
		if ($this->_from_names === null) {
			$this->_from_names = array();

			if ($this->_froms !== null) {
				$froms = $this->_froms;
			} else {
				$froms = App::getDb()->fetchAll("
					SELECT id, name, address
					FROM email_from
					ORDER BY name ASC
				");
			}

			foreach ($froms as $from) {
				$this->_from_names[$from['id']] = "{$from['name']} <{$from['address']}>";
			}
		}

		return $this->_from_names;
	}

	/**
	 * Find a EmailFrom based on the "address" part.
	 * This takes into account wildcard addresses too for catch-all type.
	 *
	 * @param  $address
	 * @return \Application\DeskPRO\Entity\EmailFrom
	 */
	public function findFromAddress($address)
	{
		$this->_loadFroms();

		$address = strtolower($address);

		#-------------------------
		# First pass looking for exact matches
		#-------------------------

		foreach ($this->_froms as $from) {
			if ($from['address'] == $address) {
				return $from;
			}
		}


		#-------------------------
		# Second pass checking with wildcard
		#-------------------------

		foreach ($this->_froms as $from) {
			if (Strings::isStarMatch($from['address'], $address)) {
				return $from;
			}
		}


		return null;
	}
}