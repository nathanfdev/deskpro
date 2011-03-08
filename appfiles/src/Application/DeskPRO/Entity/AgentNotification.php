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

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\DeskPRO\Entity;

/**
 * Tracks notification preferences for each agent on each queue.
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\AgentNotification")
 * @orm:Table(name="agent_notifications")
 */
class AgentNotification extends \Application\DeskPRO\Domain\DomainObject
{
	const NOTIFY_NEW_TICKET         = 'new_ticket';
	const NOTIFY_NEW_REPLY          = 'new_reply';
	const NOTIFY_NEW_AGENT_REPLY    = 'new_agent_reply';
	const NOTIFY_PROPERTY_CHANGE    = 'property_change';

	/**
	 * @var \Application\DeskPRO\Entity\TicketFilter
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="TicketFilter")
	 * @orm:JoinColumn(name="filter_id", referencedColumnName="id")
	 */
	protected $filter = null;

	/**
	 * @var Application\DeskPRO\Entity\Person
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Person", inversedBy="emails")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @orm:Id
	 * @orm:Column(name="notify_type", type="string", length="50")
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