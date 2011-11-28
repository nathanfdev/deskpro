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
use Orb\Util\Strings;
use Orb\Util\Numbers;
use Orb\Util\Arrays;
use Orb\Util\Web;

/**
 * Permissions are flags applied groups or specific users.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="permissions")
 */
class Permission extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The name of the permission
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="name", type="string", length=50)
	 */
	protected $name = null;

	/**
	 * The usergroup this properly belongs to. Note that a permission applies to either
	 * a person or a usergroup, never both.
	 *
	 * @var Application\DeskPRO\Entity\Usergroup
	 * @ORM_Mapping\ManyToOne(targetEntity="Usergroup")
	 * @ORM_Mapping\JoinColumn(name="usergroup_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $usergroup;

	/**
	 * The person this properly belongs to. Note that a permission applies to either
	 * a person or a usergroup, never both.
	 *
	 * @var Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person;

	/**
	 * Any numeric number (ex filesize, flag)
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="value", type="text", nullable=true)
	 */
	protected $value = null;

	public function __toString()
	{
		$str = '[' . $this->name . ':';
		if ($prop->value !== null) {
			$str .= $prop->data;
		} else {
			$str .= 'NULL';
		}
		$str .= ']';

		return $str;
	}


	/**
	 * Combine an array of permissions into a superduper array of effective permissions.
	 *
	 * @param \Application\DeskPRO\Entity\Permission[] $perms
	 * @return array
	 */
	public static function getEffectivePermissions(array $perms)
	{
		$effective_perms = array();

		foreach ($perms as $perm) {
			$k = $perm->name;
			$v = $perm->value;

			if (!Numbers::isInteger($v)) {
				$v = (int)$v;
			}

			// If it hasnt been set yet, or the one we have is "lower",
			// then take the new value.
			if (!isset($effective_perms[$k]) || (is_int($v) && $effective_perms[$k] < $v)) {
				$effective_perms[$k] = $v;
			}
		}

		return $effective_perms;
	}
}
