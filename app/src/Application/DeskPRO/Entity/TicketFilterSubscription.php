<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
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
