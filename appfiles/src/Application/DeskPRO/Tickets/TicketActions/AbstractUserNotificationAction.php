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
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

use Application\DeskPRO\Email\TicketUtil;
use Application\DeskPRO\Tickets\TicketChangeTracker;
use Application\DeskPRO\App;

/**
 * Sets agent
 */
abstract class AbstractUserNotificationAction implements ActionInterface
{
	/**
	 * @var \Application\DeskPRO\Tickets\TicketChangeTracker
	 */
	protected $tracker;
	protected $person_context;
	protected $template_suffix = '';

	public function __construct(TicketChangeTracker $tracker, $template_suffix = '')
	{
		$this->tracker = $tracker;
	}

	public function setTemplateSuffix($template_suffix)
	{
		$this->template_suffix = $template_suffix;
	}

	public function getTemplateSuffix()
	{
		return $this->template_suffix;
	}

	public function getFromAddress()
	{
		return $this->from_address;
	}

	protected function doSend($tpl, $vars, Ticket $ticket)
	{
		$person = $ticket->person;
		$parts  = $ticket->getUserParticipants();

		// Make sure everyone has their own TAC
		// It's not strictly used here, but can be used as confirmation code for registration,
		// removing themselves from the ticket, etc
		foreach ($parts as $p) {
			TicketUtil::getTacForPerson($ticket, $p);
		}
		
		$vars['ticket'] = $ticket;
		$vars['person'] = $person;
		$vars['participants'] = $parts;
		$vars['access_code'] = $ticket->getAccessCode();

		$messages = App::getEntityRepository('DeskPRO:TicketMessage')->getTicketMessages($ticket,array(
			'limit' => 25,
			'order' => 'DESC',
			'with_notes' => false
		));
		$vars['messages'] = $messages;

		$tpl_suffix = $this->getTemplateSuffix();
		App::getTranslator()->setTemporaryLocale($person->getLocale(), function($tr, $locale) use ($tpl, $vars, $ticket, $parts, $tpl_suffix) {
			$email_subject = $tr->phrase($vars['email_subject']);
			$email_body = App::get('templating')->render($tpl.$tpl_suffix.'.html.twig', $vars);

			$message = App::getMailer()->createMessage();
			$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
			foreach ($parts as $part) {
				$message->addCc($part['email_address'], $part->person->getDisplayName());
			}
			$message->setSubject($email_subject);
			$message->setBody($email_body, 'text/html');
			$message->enableQueueHint();

			App::getMailer()->send($message);
		});
	}

	/**
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $other_action
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function merge(ActionInterface $other_action)
	{
		$template_suffix = $other_action->getTemplateSuffix();
		if (!$template_suffix) {
			$template_suffix = $this->getTemplateSuffix();
		}

		return new self($this->tracker, $other_action->getFromAddress(), $template_suffix);
	}
}