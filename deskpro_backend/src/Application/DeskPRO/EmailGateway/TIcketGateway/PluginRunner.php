<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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
			throw new \BadMethodCallException("Unknow method `$method`");
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