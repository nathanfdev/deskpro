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
 * Flagged tickets
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketFlagged")
 * @ORM_Mapping\Table(name="tickets_flagged")
 */
class TicketFlagged extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\Column(name="ticket_id", type="integer")
	 */
	protected $ticket_id = null;

	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\Column(name="person_id", type="integer")
	 */
	protected $person_id = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="color", type="string", length=20)
	 */
	protected $color = 'blue';
}