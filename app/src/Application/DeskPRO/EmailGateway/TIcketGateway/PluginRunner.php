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
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\TicketGateway;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;

class PluginRunner implements EventSubscriberInterface
{
	/**
	 * @var \Application\DeskPRO\EmailGateway\TicketGateway $gateway
	 */
	protected $gateway;

	protected $agent_prop_set;

	/**
	 * @param \Application\DeskPRO\EmailGateway\TicketGateway $gateway
	 */
	public function __construct(TicketGateway $gateway)
	{
		$this->gateway = $gateway;
		$this->gateway->getEventManager()->addSubscriber($this);
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 */
	static function getSubscribedEvents()
	{
		return array(
			TicketGateway::EVENT_EVENT,
			TicketGateway::EVENT_BEFORE_RUN_ACTION,
			TicketGateway::EVENT_RUN_ACTION,
			TicketGateway::EVENT_BEFORE_NEWREPLY,
			TicketGateway::EVENT_NEWREPLY,
			TicketGateway::EVENT_BEFORE_NEWTICKET,
			TicketGateway::EVENT_NEWTICKET,
		);
	}


	public function __call($method, $args)
	{
		if (!in_array($method, $this->getSubscribedEvents())) {
			throw new \BadMethodCallException("Unknown method `$method`");
		}

		if (empty($args[0]) OR !($args[0] instanceof GatewayEvent)) {
			throw new \InvalidArgumentException("First and only argument for `$method` must be a GatewayEvent");
		}
		$ev = $args[0];

		#------------------------------
		# Agent prop setter
		#------------------------------

		if ($method == TicketGateway::EVENT_BEFORE_NEWREPLY) {
			if ($ev->persion['is_agent']) {
				$this->agent_prop_set = new AgentPropSet($ev->ticket, $ev->person, $ev->email_info['body']);
				$ev->email_info['body'] = $this->agent_prop_set->getProcessedBody();
			}
		} elseif ($method == TicketGateway::EVENT_NEWREPLY) {
			if ($this->agent_prop_set) {
				$this->agent_prop_set->applyChanges();
				$this->agent_prop_set = null;
			}
		}
	}
}
