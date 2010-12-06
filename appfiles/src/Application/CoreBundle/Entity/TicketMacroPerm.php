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
 * Ticket macro permissions
 *
 * @orm:Entity
 * @orm:Table(name="ticket_macros_perms",
 *     indexes={@orm:Index(name="object_idx", columns={"object_type", "object_id"})}
 * )
 */
class TicketMacroPerm extends \Application\DeskPRO\Domain\DomainObject
{
	const TYPE_DEPARTMENT = 'department';
	const TYPE_USERGROUP = 'usergroup';
	const TYPE_PERSON = 'person';

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var int
	 * @orm:Column(name="macro_id", type="integer", nullable=true)
	 */
	protected $macro_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\TicketMacro
	 * @orm:OneToOne(targetEntity="TicketMacro")
	 * @orm:JoinColumn(name="macro_id", referencedColumnName="id")
	 */
	protected $macro = null;

	/**
	 * The type of object this is attached to (should be the table name of
	 * the super type, eg: tickets, people, organizations).
	 *
	 * @var string
	 * @orm:Column(name="object_type", type="string", length=50)
	 */
	protected $object_type;

	/**
	 * The ID of the object this is attached to.
	 *
	 * @var int
	 * @orm:Column(name="object_id", type="integer")
	 */
	protected $object_id;
}