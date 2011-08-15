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

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A usergroup is any way to group related users together. Not necessarily just for permissions.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Usergroup")
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="usergroups")
 */
class Usergroup extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The sysname of the everyone group
	 */
	const EVERYONE_NAME = 'everyone';

	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 * 
	 */
	protected $id = null;

	/**
	 * Title of the usergroup
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * A note or description about the usergroup
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="note", type="text")
	 */
	protected $note = '';

	/**
	 * Is this an agent group?
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_agent_group", type="boolean")
	 */
	protected $is_agent_group = false;

	/**
	 * When non-null, the group is a special system group (hidden from most interfaces).
	 * 
	 * @var bool
	 * @ORM_Mapping\Column(name="sys_name", type="string", length="50", nullable=true)
	 */
	protected $sys_name = null;

	/**
	 * Properties attached to this usergroup
	 *
	 * @var Application\DeskPRO\Entity\UsergroupProperty
	 * @ORM_Mapping\OneToMany(targetEntity="UsergroupProperty", mappedBy="usergroup", cascade={"persist", "remove"})
	 */
	protected $properties;


	public function __construct()
	{
		$this->properties = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function fromArray(array $values)
	{
		if (isset($values['permissions']) AND is_array($values['permissions'])) {
			$em = App::getOrm();
			foreach ($values['permissions'] as $name => $val) {
				$prop = new \Application\DeskPRO\Entity\UsergroupPropertyPermission();
				$prop['name'] = $name;
				if (is_bool($val)) {
					$prop['flag'] = $val;
				} else {
					$prop['data'] = $val;
				}

				$this->properties->add($prop);
			}

			unset($values['permissions']);
		}

		parent::fromArray($values);
	}


	/**
	 * Generate a key for a set of usergroups. These same usergroups
	 * will always generate the same key.
	 *
	 * @static
	 * @param array $usergroups Array of usergroup IDs or usergroup objects
	 * @return string
	 */
	public static function generateUsergroupSetKey(array $usergroups)
	{
		$usergroup_ids = array();
		foreach ($usergroups as $ug) {
			if (is_object($ug)) {
				$usergroup_ids[] = $ug['id'];
			} else {
				$usergroup_ids[] = (int)$ug;
			}
		}

		if ($usergroup_ids) {
			$usergroup_ids = array_unique($usergroup_ids, \SORT_NUMERIC);
			sort($usergroup_ids, \SORT_NUMERIC);
		} else {
			$usergroup_ids = array(0);
		}

		return md5(implode(',', $usergroup_ids));
	}
}