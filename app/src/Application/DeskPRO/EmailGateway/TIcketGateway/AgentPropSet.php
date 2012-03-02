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
