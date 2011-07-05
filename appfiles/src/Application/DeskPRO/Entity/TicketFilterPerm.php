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
 * Ticket macro permissions
 *
 * @orm:Entity
 * @orm:Table(name="ticket_filters_perms",
 *     indexes={@orm:Index(name="object_idx", columns={"object_type", "object_id"})}
 * )
 */
class TicketFilterPerm extends \Application\DeskPRO\Domain\DomainObject
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
	 * @var \Application\DeskPRO\Entity\TicketFilter
	 * @orm:OneToOne(targetEntity="TicketFilter")
	 * @orm:JoinColumn(name="filter_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $filter = null;

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