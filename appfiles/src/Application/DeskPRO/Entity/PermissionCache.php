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
	 * @ORM_Mapping\Column(name="usergroup_ids", type="string", length=1000)
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
