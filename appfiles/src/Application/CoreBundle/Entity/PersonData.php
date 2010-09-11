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
 * A PersonData is any piece of additional data we store on a Person. It can be
 * anything from birthdays to remote resource identities.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="person_data")
 */
class PersonData extends \DeskPRO\Domain\DomainObject
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
	 * @var Application\CoreBundle\Entity\Person
	 * @ManyToOne(targetEntity="Person", inversedBy="email_addresses")
	 * @JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;


	/**
	 * The field that describes this data.
	 *
	 * @var Application\CoreBundle\Entity\PersonField
	 * @ManyToOne(targetEntity="PersonField")
	 * @JoinColumn(name="person_field_id", referencedColumnName="id")
	 */
	protected $person_field;


	/**
	 * If this field is mapped to a remote obejct, that remote object is this.
	 * 
	 * @var Application\CoreBundle\Entity\RemoteObject
	 * @ManyToOne(targetEntity="RemoteObject")
	 * @JoinColumn(name="remote_object_id", referencedColumnName="id")
	 */
	protected $remote_object = null;


	/**
	 * The value of this data
	 * 
	 * @var string
	 * @Column(name="value", type="text")
	 */
	protected $value;

	
	/**
	 * The raw value of this data might include supplementary data that is used
	 * in various transformations etc. For example, if this is an identity then
	 * maybe $value is a username and this contains the raw ID.
	 *
	 * @Column(name="raw_value", type="array", nullable=true)
	 */
	protected $raw_value = null;
}