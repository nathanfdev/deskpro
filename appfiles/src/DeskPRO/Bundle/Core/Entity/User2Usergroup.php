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

namespace DeskPRO\Bundle\Core\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A "profile" is a record in the database that stores information about a person.
 *
 * @Entity
 * @Table(name="user2usergroup")
 */
class User2Usergroup extends \DeskPRO\Bundle\Core\Entity\Entity
{
	/**
	 * The user ID
	 *
	 * @var int
	 * @Column(name="user_id", type="integer")
	 */
	protected $user_id = null;

	/**
	 * @var User
	 * @OneToOne(targetEntity="User")
	 * @JoinColumn(name="user_id", referencedColumnName="id")
	 */
	protected $user = null;

	/**
	 * The usergroup ID
	 *
	 * @var int
	 * @Column(name="user_id", type="integer")
	 */
	protected $usergroup_id = null;

	/**
	 * @var Usergroup
	 * @OneToOne(targetEntity="Usergroup")
	 * @JoinColumn(name="usergroup_id", referencedColumnName="id")
	 */
	protected $usergroup = null;
}