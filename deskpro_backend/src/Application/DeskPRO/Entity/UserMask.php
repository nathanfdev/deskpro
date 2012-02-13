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

/**
 * A usermask is applied to a specific user to override permissions set by a usergroup.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="user_masks")
 */
class UserMask extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\OneToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 * @ORM_Mapping\Id
	 */
	protected $person;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="overrides", type="array")
	 */
	protected $overrides = array();
}