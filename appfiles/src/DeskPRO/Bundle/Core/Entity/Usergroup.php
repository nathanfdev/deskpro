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
 * A "user" is a person in the database who can log in and has access to the interfaces.
 *
 * @Entity
 * @Table(name="usergroups")
 */
class Usergroup extends \DeskPRO\Bundle\Core\Entity\Entity
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @Id
	 * @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;


	/**
	 * Title of the usergroup
	 *
	 * @var string
	 * @Column(name="title", type="string", length=255)
	 */
	protected $title;


	/**
	 * A note or description about the usergroup
	 *
	 * @var string
	 * @Column(name="note", type="text")
	 */
	protected $note;


	/**
     * @ManyToMany(targetEntity="User", inversedBy="usergroups")
	 * @JoinTable(name="user2usergroups",
	 *     joinColumns={@JoinColumn(name="usergroup_id", referencedColumnName="id")},
     *     inverseJoinColumns={@JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     */
    protected $users;


	/**
	 * Is this user a tech that can use the tech interface?
	 *
	 * @var bool
	 * @Column(name="p_is_tech", type="boolean", default=false)
	 */
	protected $is_tech = false;


	/**
	 * Is this user an admin that can use the admin features?
	 *
	 * @var bool
	 * @Column(name="p_is_admin", type="boolean", default=false)
	 */
	protected $is_admin = false;



	/**
	 * Get an array of yes/no permissions for this usergroup.
	 *
	 * @return array
	 */
	public function getPermissionArray()
	{
		$arr = array(
			'is_tech' => $this->is_tech,
			'is_admin' => $this->is_admin,
		);

		return $arr;
	}
}