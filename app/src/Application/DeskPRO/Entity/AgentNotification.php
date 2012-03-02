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

use Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use Application\DeskPRO\Entity;

/**
 * Tracks notification preferences for each agent on each queue.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\AgentNotification")
 * @ORM_Mapping\Table(name="agent_notifications")
 */
class AgentNotification extends \Application\DeskPRO\Domain\DomainObject
{
	const NOTIFY_NEW_TICKET         = 'new_ticket';
	const NOTIFY_NEW_REPLY          = 'new_reply';
	const NOTIFY_NEW_AGENT_REPLY    = 'new_agent_reply';
	const NOTIFY_PROPERTY_CHANGE    = 'property_change';

	/**
	 * @var \Application\DeskPRO\Entity\TicketFilter
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\ManyToOne(targetEntity="TicketFilter")date_resolved
	 * @ORM_Mapping\JoinColumn(name="filter_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $filter = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", inversedBy="emails")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="notify_type", type="string", length=50)
	 */
	protected $notify_type = false;



	/**
	 * Reduce notification types down to the most encompassing.
	 * For example, if a reply is made and also changed some properties,
	 * then the only type that matters is 'reply'.
	 *
	 * @static
	 * @param array $notify_types
	 * @return array
	 */
	public static function reduceNotificationTypes(array $notify_types)
	{
		if (in_array('new_ticket', $notify_types)) {
			$notify_types = array('new_ticket');
		} elseif (in_array('new_reply', $notify_types)) {
			$notify_types = array('new_reply');
		} elseif (in_array('new_agent_reply', $notify_types)) {
			$notify_types = array('new_agent_reply');
		} elseif (in_array('property_change', $notify_types)) {
			$notify_types = array('property_change');
		}

		return $notify_types;
	}
}
