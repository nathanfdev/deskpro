<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

use Application\DeskPRO\Tickets\TicketChangeTracker;

use Application\DeskPRO\App;

/**
 * Sets agent
 */
abstract class AbstractAgentNotificationAction implements ActionInterface, PersonContextInterface
{
	/**
	 * @var \Application\DeskPRO\Tickets\TicketChangeTracker
	 */
	protected $tracker;
	protected $send_to;
	protected $real_send_to;
	protected $from_address;
	protected $person_context;
	protected $template_suffix = '';

	public function __construct(TicketChangeTracker $tracker, array $send_to, $from_address, $template_suffix = '')
	{
		$this->tracker = $tracker;
		$this->send_to = $send_to;
		$this->from_address = $from_address;
	}


	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}

	public function setTemplateSuffix($template_suffix)
	{
		$this->template_suffix = $template_suffix;
	}

	public function getTemplateSuffix()
	{
		return $this->template_suffix;
	}

	public function getSendTo()
	{
		return $this->send_to;
	}

	public function getRealSendTo($recalc = false)
	{
		if ($this->real_send_to !== null) return $this->real_send_to;

		$agent_ids = array();

		foreach ($this->send_to as $send_to) {
			if ($send_to == 'assigned_agent') {
				if ($ticket['agent_id']) $agent_ids[] = $ticket['agent_id'];
			} elseif ($send_to == 'assigned_agent_team') {
				if ($ticket['agent_team_id']) {
					$agent_ids = array_merge($agent_ids, App::getEntityRepository('DeskPRO:AgentTeam')->getMemberIds($ticket['agent_team_id']));
				}
			} elseif (strpos($send_to, 'agent.') === 0) {
				list (, $agent_id) = explode('.', $send_to, 2);
				$agent_ids[] = $agent_id;
			} elseif (strpos($send_to, 'agent_team.') === 0) {
				list (, $agent_team_id) = explode('.', $send_to, 2);
				$agent_ids = array_merge($agent_ids, App::getEntityRepository('DeskPRO:AgentTeam')->getMemberIds($agent_team_id));
			}
		}

		$agent_ids = array_unique($agent_ids);

		$this->real_send_to = $agent_ids;

		return $agent_ids;
	}

	public function getFromAddress()
	{
		return $this->from_address;
	}


	/**
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $other_action
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function merge(ActionInterface $other_action)
	{
		$send_to = array_merge($this->getSendTo(), $other_action->getSendTo());
		$template_suffix = $other_action->getTemplateSuffix();
		if (!$template_suffix) {
			$template_suffix = $this->getTemplateSuffix();
		}

		return new self($this->tracker, $send_to, $other_action->getFromAddress(), $template_suffix);
	}
}