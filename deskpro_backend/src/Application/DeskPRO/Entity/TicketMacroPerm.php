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

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * Ticket macro permissions
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="ticket_macros_perms",
 *     indexes={@ORM_Mapping\Index(name="object_idx", columns={"object_type", "object_id"})}
 * )
 */
class TicketMacroPerm extends \Application\DeskPRO\Domain\DomainObject
{
	const TYPE_DEPARTMENT = 'department';
	const TYPE_USERGROUP = 'usergroup';
	const TYPE_PERSON = 'person';

	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 * 
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketMacro
	 * @ORM_Mapping\OneToOne(targetEntity="TicketMacro")
	 * @ORM_Mapping\JoinColumn(name="macro_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $macro = null;

	/**
	 * The type of object this is attached to (should be the table name of
	 * the super type, eg: tickets, people, organizations).
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="object_type", type="string", length=50)
	 */
	protected $object_type;

	/**
	 * The ID of the object this is attached to.
	 *
	 * @var int
	 * @ORM_Mapping\Column(name="object_id", type="integer")
	 */
	protected $object_id;
}