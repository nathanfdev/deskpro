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
	protected $send_to;
	protected $real_send_to;
	protected $person_context;
	protected $template_suffix = '';

	public function __construct(TicketChangeTracker $tracker, array $send_to, $template_suffix = '')
	{
		$this->tracker = $tracker;
		$this->send_to = $send_to;
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

		$user_ids = array();

		$ticket = $this->tracker->getTicket();

		foreach ($this->send_to as $send_to) {
			if ($send_to == 'person') {
				$user_ids[] = $ticket->person['id'];
			} elseif ($send_to == 'people_participants') {
				foreach ($ticket->getUserParticipants() as $part) {
					$user_ids[] = $part->person['id'];
				}
			}
		}

		$user_ids = array_unique($user_ids);

		$this->real_send_to = $user_ids;

		return $user_ids;
	}

	public function getFromAddress()
	{
		return $this->from_address;
	}

	protected function doSend($tpl, $vars, Ticket $ticket, Person $person)
	{
		$tac = TicketUtil::getTacForPerson($ticket, $person);
		$vars['ticket'] = $ticket;
		$vars['person'] = $person;
		$vars['access_code'] = $tac['code'];
		$vars['access_code_full'] = $ticket['ref'] . '-' . $tac['code'];

		$messages = App::getEntityRepository('DeskPRO:TicketMessage')->getTicketMessages($ticket,array(
			'limit' => 25,
			'order' => 'DESC',
			'with_notes' => false
		));
		$vars['messages'] = $messages;

		$tpl_suffix = $this->getTemplateSuffix();
		App::getTranslator()->setTemporaryLocale($person->getLocale(), function($tr, $locale) use ($tpl, $vars, $ticket, $person, $tpl_suffix) {
			$email_subject = $tr->phrase($vars['email_subject']);
			$email_body = App::get('templating')->render($tpl.$tpl_suffix.'.html.twig', $vars);

			$message = App::getMailer()->createMessage();
			$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
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
		$send_to = array_merge($this->getSendTo(), $other_action->getSendTo());
		$template_suffix = $other_action->getTemplateSuffix();
		if (!$template_suffix) {
			$template_suffix = $this->getTemplateSuffix();
		}

		return new self($this->tracker, $send_to, $other_action->getFromAddress(), $template_suffix);
	}
}