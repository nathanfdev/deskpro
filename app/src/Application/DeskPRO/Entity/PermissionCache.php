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
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;

use Application\DeskPRO\People\PersonContextInterface;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

/**
 * A cache of various permissions for a given set of usergroups. For example,
 * a computed array of category ID's 1,3,5 has access to.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\PermissionCache")
 * @ORM_Mapping\Table(name="permissions_cache")
 */
class PermissionCache extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The type of permissions cache
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="name", type="string", length=255)
	 * @ORM_Mapping\Id
	 */
	protected $name;

	/**
	 * Usergroup key is an md5() of all usergroup ID's concatenated
	 * with a command in asending order.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="usergroup_key", type="string", length=32)
	 * @ORM_Mapping\Id
	 */
	protected $usergroup_key;

	/**
	 * A comma-separated list of usergroup_ids this cache applies to
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="usergroup_ids", type="text")
	 */
	protected $usergroup_ids = '';

	/**
	 * Permission data
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="perms", type="object")
	 */
	protected $perms = array();

	protected $_usergroup_ids = null;

	public static function newFromLoader(\Application\DeskPRO\People\PermissionLoader\AbstractLoader $loader, $person_id = 0)
	{
		$obj = new self();
		$obj['name'] = Util::getBaseClassname($loader);
		$obj['usergroup_ids'] = $loader->getUsergroupIds();
		if ($person_id && $loader instanceof PersonContextInterface) {
			$obj->appendKeyId($person_id);
		}
		$obj->perms = $loader;

		return $obj;
	}


	public function setUsergroupIds(array $ids)
	{
		sort($ids, SORT_NUMERIC);
		$this->_usergroup_ids = $ids;

		$this->usergroup_ids = implode(',', $ids);
		$this->usergroup_key = self::generateUsergroupSetKey($this->_usergroup_ids);
	}

	public function getUsergroupIds()
	{
		if ($this->_usergroup_ids === null) {
			$this->_usergroup_ids = explode(',', $this->usergroup_ids);
		}

		return $this->_usergroup_ids;
	}

	public function appendKeyId($id)
	{
		$this->setModelField('usergroup_key', $this->usergroup_key . $id);
	}


	/**
	 * Generate a key for a set of usergroups. These same usergroups
	 * will always generate the same key.
	 *
	 * @static
	 * @param array $usergroup_ids
	 * @return string
	 */
	public static function generateUsergroupSetKey(array $usergroup_ids)
	{
		return Usergroup::generateUsergroupSetKey($usergroup_ids);
	}
}
