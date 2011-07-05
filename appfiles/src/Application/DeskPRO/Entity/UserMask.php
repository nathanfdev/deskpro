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

/**
 * A usermask is applied to a specific user to override permissions set by a usergroup.
 *
 * @orm:Entity
 * @orm:Table(name="user_masks")
 */
class UserMask extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:OneToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 * @orm:Id
	 */
	protected $person;

	/**
	 * @var string
	 * @orm:Column(name="overrides", type="array")
	 */
	protected $overrides = array();
}