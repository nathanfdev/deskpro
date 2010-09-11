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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * This record defines the relationship between a Person and a Usersource.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="person_auth")
 */
class PersonAuth extends \DeskPRO\Domain\DomainObject
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
	 * The person this auth record belongs to.
	 *
	 * @var Application\CoreBundle\Entity\Person
	 * @OneToOne(targetEntity="Person")
	 * @JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;


	/**
	 * The usersource this auth record belongs to.
	 * 
	 * @var Application\CoreBundle\Entity\Usersource
	 * @OneToOne(targetEntity="Usersource")
	 * @JoinColumn(name="usersource_id", referencedColumnName="id")
	 */
	protected $usersource;


	/**
	 * The user id in the remote source (user id, an openid URL etc). Can be
	 * anything.
	 * 
	 * @var string
	 * @Column(name="identity", type="string", length=255)
	 */
	protected $identity;


	/**
	 * The human-friendly version of the identity. So if the identity is a UserID,
	 * this might be the username.
	 *
	 * @var string
	 * @Column(name="identity_friendly", type="string", length=255)
	 */
	protected $identity_friendly;


	/**
	 * The userinfo (if any) we got back from the remote source.
	 * 
	 * @var array
	 * @Column(name="userinfo", type="array", nullable=true)
	 */
	protected $userinfo = null;
}