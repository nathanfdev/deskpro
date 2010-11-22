<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Entity;

/**
 * A usermask is applied to a specific user to override permissions set by a usergroup.
 *
 * @Entity
 * @Table(name="user_masks")
 */
class UserMask extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @Id @Column(name="person_id", type="integer")
	 */
	protected $person_id = null;

	/**
	 * @var string
	 * @Column(name="overrides", type="array")
	 */
	protected $overrides = array();
}