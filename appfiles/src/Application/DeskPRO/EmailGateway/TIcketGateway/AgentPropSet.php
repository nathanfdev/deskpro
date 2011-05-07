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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

class AgentPropSet
{
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $ticket;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @var string
	 */
	protected $email_body;

	/**
	 * @var array
	 */
	protected $changes = array();

	public function __construct(Ticket $ticket, Person $person, $email_body)
	{
		$this->ticket = $ticket;
		$this->person = $person;
		$this->email_body = $email_body;
	}

	public function getProcessedBody()
	{
		$props = array(
			'ticket-department' => array('department', 'DeskPRO:Department', 'findByTitle'),
			'ticket-category'   => array('category', 'DeskPRO:TicketCategory', 'findByTitle'),
			'ticket-product'    => array('product', 'DeskPRO:Product', 'findByTitle'),
			'ticket-workflow'   => array('workflow', 'DeskPRO:TicketWorkflow', 'findByTitle'),
			'ticket-agent'      => array('agent', 'DeskPRO:Person', 'findAgentByName'),
			'ticket-agent-team' => array('agent_team', 'DeskPRO:AgentTeam', 'findByName'),
			'ticket-priority'   => array('priority', 'DeskPRO:TicketPriority', 'findByTitle'),
		);

		foreach ($props as $k => $info) {
			$m = null;
			if (!preg_match("/##" . preg_quote($props) . ":(.*?)(\n|<)/i", $this->email_body, $m)) {
				continue;
			}

			$val = trim($m[1]);
			if (!$val) {
				$this->changes[$info[0]] = null;
			} else {
				$method = $info[2];
				$found = App::getEntityRepository($info[1])->$method($val);
				$this->changes[$info[0]] = $found;
			}

			$this->email_body = str_replace($m[0], '', $this->email_body);
		}

		return $this->email_body;
	}

	public function applyChanges()
	{
		foreach ($this->changes as $prop => $val) {
			$this->ticket[$prop] = $val;
		}
	}
}