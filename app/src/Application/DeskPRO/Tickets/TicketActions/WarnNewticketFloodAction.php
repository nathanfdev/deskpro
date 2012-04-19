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
 * @subpackage Tickets
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

use Application\DeskPRO\Tickets\TicketChangeTracker;
use Application\DeskPRO\Translate\DelegatePhrase;
use Application\DeskPRO\App;

class WarnNewticketFloodAction extends AbstractUserNotificationAction
{
	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$person = $ticket->person;

		$vars['ticket'] = $ticket;
		$vars['person'] = $person;
		$vars['participants'] = $parts;
		$vars['access_code'] = $ticket->getAccessCode();

		$from_address = App::getSetting('core.default_from_email');

		App::getTranslator()->setTemporaryLanguage($person->getLanguage(), function($tr) use ($tpl, $vars, $from_address, $ticket, $person, $parts, $tpl_suffix, $only_cc_ids) {
			$email_subject = 'Warning: Confirmation emails turned off';
			$email_body = App::get('templating')->render('gateway-autoresponse-warn.html.twig', $vars);

			$message->setSubject($email_subject);
			$message->setBody($email_body, 'text/html');
			$message->setFrom($from_address);

			App::getMailer()->send($message);
		});
	}

	/**
	 * @return string
	 */
	public function getDescription($as_html = true)
	{
		$tr = App::getTranslator();
		return $tr->phrase('agent.tickets.send_flood_warning_action');
	}
}
