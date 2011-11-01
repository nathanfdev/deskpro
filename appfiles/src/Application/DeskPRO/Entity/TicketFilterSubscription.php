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
 * A simple record that just holds filter subscriptions for agents.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketFilterSubscription")
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

	/**
	 * @ORM_Mapping\Column(name="email_new", type="boolean")
	 */
	protected $email_new = false;

	/**
	 * @ORM_Mapping\Column(name="email_user_activity", type="boolean")
	 */
	protected $email_user_activity = false;

	/**
	 * @ORM_Mapping\Column(name="email_agent_activity", type="boolean")
	 */
	protected $email_agent_activity = false;

	/**
	 * @ORM_Mapping\Column(name="email_property_change", type="boolean")
	 */
	protected $email_property_change = false;

	/**
	 * @ORM_Mapping\Column(name="alert_new", type="boolean")
	 */
	protected $alert_new = false;

	/**
	 * @ORM_Mapping\Column(name="alert_user_activity", type="boolean")
	 */
	protected $alert_user_activity = false;

	/**
	 * @ORM_Mapping\Column(name="alert_agent_activity", type="boolean")
	 */
	protected $alert_agent_activity = false;

	/**
	 * @ORM_Mapping\Column(name="alert_property_change", type="boolean")
	 */
	protected $alert_property_change = false;

}
