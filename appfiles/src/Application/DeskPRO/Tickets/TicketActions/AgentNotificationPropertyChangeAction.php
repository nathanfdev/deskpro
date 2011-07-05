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
use Application\DeskPRO\Tickets\TicketChangeSummary;
use Application\DeskPRO\Translate\DelegatePhrase;
use Application\DeskPRO\App;

/**
 * Sets agent
 */
class AgentNotificationPropertyChangeAction extends AbstractAgentNotificationAction
{
	/**
	 * Get the default template name to use
	 *
	 * @return void
	 */
	public function getDefaultTemplate()
	{
		return 'DeskPRO:emails_agent:new-ticket';
	}
	
	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$agent_ids = $this->getRealSendTo();

		if (!$agent_ids) {
			return;
		}

		foreach ($agent_ids as $agent_id => $tpl) {
			$agent = App::getEntityRepository('DeskPRO:Person')->find($agent_id);

			$vars = array(
				'ticket_changes' => new TicketChangeSummary($this->tracker),
				'email_subject' => new DelegatePhrase('core_tickets_agent_email.subject_ticket_updated', array('ticket_subject' => $ticket['subject'])),
			);

			$this->doSend($tpl, $vars, $ticket, $person);
		}
	}
}