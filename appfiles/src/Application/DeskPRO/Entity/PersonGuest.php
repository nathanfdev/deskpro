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

namespace Application\DeskPRO\Entity;

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\DeskPRO\Entity\UsergroupPropertyPermission;

/**
 * A website visitor when we have no information about them
 */
class PersonGuest extends Person
{
	public function __construct()
	{
		$this->id = 0;
		$this->_usergroup_ids = array();
		$this->usergroups = array();
		$this->timezone = App::getSetting('core.default_timezone');
	}

	public function getUsergroups()
	{
		return array();
	}

	public function isGuest()
	{
		return true;
	}

	/** @orm:PrePersist */
	public function noPersist()
	{
		throw new \BadMethodCallException('A PersonGuest cannot be persisted');
	}
}
