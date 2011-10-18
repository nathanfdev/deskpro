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
 * Subscriptions record which filters agents are interested in
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="ticket_filter_subscriptions")
 */
class TicketFilterSubscription extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\TicketFilter
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\ManyToOne(targetEntity="TicketFilter", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="filter_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $filter;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person;
}
