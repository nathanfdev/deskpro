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

namespace DeskPRO\Entities;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A "profile" is a record in the database that stores information about a person.
 *
 * @Entity
 * @Table(name="users")
 */
class User extends Entity
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	
	/**
	 * @var Profile
	 * @OneToOne(targetEntity="Profile", mappedBy="user")
	 * @JoinColumn(name="profile_id", referencedColumnName="id")
	 */
	protected $profile;
}