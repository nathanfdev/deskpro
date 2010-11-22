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
 * Departments
 *
 * @Entity
 * @Table(name="ticket_macros_perms")
 */
class TicketMacroPerm extends \DeskPRO\Domain\DomainObject
{
	const TYPE_DEPARTMENT = 'department';
	const TYPE_USERGROUP = 'usergroup';
	const TYPE_PERSON = 'person';

	/**
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var int
	 * @Column(name="macro_id", type="integer", nullable=true)
	 */
	protected $macro_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\Macro
	 * @OneToOne(targetEntity="Macro")
	 * @JoinColumn(name="macro_id", referencedColumnName="id")
	 */
	protected $macro = null;

	/**
	 * The type of object this is attached to (should be the table name of
	 * the super type, eg: tickets, people, organizations).
	 *
	 * @var string
	 * @Column(name="object_type", type="string", length=50)
	 */
	protected $object_type;

	/**
	 * The ID of the object this is attached to.
	 *
	 * @var int
	 * @Column(name="object_id", type="integer")
	 */
	protected $object_id;
}