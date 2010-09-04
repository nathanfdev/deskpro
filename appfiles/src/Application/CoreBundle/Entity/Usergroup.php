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

namespace DeskPRO\Bundle\CoreBundle\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A usergroup is any way to group related users together. Not necessarily just for permissions.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="usergroups")
 */
class Usergroup extends \DeskPRO\Domain\DomainObject
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
	protected $note = '';


	/**
	 * Properties attached to this usergroup
	 *
	 * @var DeskPRO\Bundle\CoreBundle\Entity\UsergroupProperty
	 * @OneToOne(targetEntity="UsergroupProperty", mappedBy="usergroup")
	 */
	protected $properties;


	public function init()
	{
		$this->properties = new \Doctrine\Common\Collections\ArrayCollection();
	}
}