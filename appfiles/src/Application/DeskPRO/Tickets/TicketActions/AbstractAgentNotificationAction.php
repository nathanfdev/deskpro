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
abstract class AbstractAgentNotificationAction implements ActionInterface
{
	/**
	 * @var \Application\DeskPRO\Tickets\TicketChangeTracker
	 */
	protected $tracker;
	protected $send_to;
	protected $real_send_to;
	protected $person_context;
	protected $template_name = '';
	protected $template_suffix = '';

	public function __construct(TicketChangeTracker $tracker, array $send_to, $custom_template = null, $template_suffix = '')
	{
		$this->tracker = $tracker;
		$this->send_to = $send_to;
		$this->template_name = $custom_template;
		$this->template_suffix = $template_suffix;
	}

	
	/**
	 * When this rule is merged, we need to take into account previously set
	 * agents and the custom template the other rule might've set
	 * 
	 * @param array $real_send_to
	 * @return void
	 */
	public function mergeRealSendTo(array $real_send_to)
	{
		$this->getRealSendTo();
		$this->real_send_to = array_merge($real_send_to, $this->real_send_to);
	}


	/**
	 * Get the default template name to use
	 * 
	 * @return void
	 */
	abstract public function getDefaultTemplate();

	
	/**
	 * Get the template to use
	 *
	 * @return string
	 */
	public function getTemplateName()
	{
		if (!$this->template_name) {
			$tpl = $this->getDefaultTemplate();
			if ($this->template_suffix) {
				$tpl .= '-' . $this->template_suffix;
			}

			return $tpl;
		}

		return $this->template_name;
	}


	/**
	 * A collection modifier may set a template suffix that'll be used if a custom
	 * template isnt
	 *
	 * @param  $template_suffix
	 * @return void
	 */
	public function setTemplateSuffix($template_suffix)
	{
		$this->template_suffix = $template_suffix;
	}


	/**
	 * Gets thet template suffix
	 *
	 * @return string
	 */
	public function getTemplateSuffix()
	{
		return $this->template_suffix;
	}


	/**
	 * Get the original send_to for this rule. Note that this is largely useless because
	 * of the way we merge in realSendTo
	 *
	 * @return array
	 */
	public function getSendTo()
	{
		return $this->send_to;
	}


	/**
	 * Calculate an array of "real" agentids we're sending to, and the template they should get
	 * 
	 * @return
	 */
	public function getRealSendTo()
	{
		//TODO test until agent prefs is done and can edit
		$this->real_send_to = null;
		$this->send_to = array();
		$agent_ids = App::getDb()->fetchAllCol("SELECT id FROM people WHERE is_agent = 1 ORDER BY id ASC LIMIT 2");

		if ($this->real_send_to !== null) return $this->real_send_to;

		$ticket = $this->tracker->getTicket();

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

		$this->real_send_to = array();
		foreach ($agent_ids as $id) {
			$this->real_send_to[$id] = $this->getTemplateName();
		}

		return $this->real_send_to;
	}


	public function getFromAddress()
	{
		return $this->from_address;
	}

	
	protected function doSend($tpl, $vars, Ticket $ticket, Person $person)
	{
		if (!$person->getPrimaryEmailAddress()) {
			return;
		}

		$tac = TicketUtil::getTacForPerson($ticket, $person);
		$vars['ticket'] = $ticket;
		$vars['person'] = $person;
		$vars['tac'] = $tac;

		$messages = App::getEntityRepository('DeskPRO:TicketMessage')->getTicketMessages($ticket,array(
			'limit' => 25,
			'order' => 'DESC',
			'with_notes' => true
		));
		$vars['messages'] = $messages;

		$tpl_suffix = $this->getTemplateSuffix();
		$tr = App::getTranslator();
		//App::getTranslator()->setTemporaryLocale($person->getLocale(), function($tr, $locale) use ($tpl, $vars, $ticket, $person, $tpl_suffix) {
			$email_subject = $tr->phrase($vars['email_subject']);
			$email_body = App::get('templating')->render($tpl.$tpl_suffix.'.html.twig', $vars);

			$message = App::getMailer()->createMessage();
			$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
			$message->setSubject($email_subject);
			$message->setBody($email_body, 'text/html');
			$message->getHeaders()->get('Message-ID')->setId($tac->getUniqueEmailMessageId());
			$message->enableQueueHint();

			App::getMailer()->send($message);
		//});
	}

	/**
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $other_action
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function merge(ActionInterface $other_action)
	{
		$other_action->mergeRealSendTo($this->getRealSendTo());
		return $other_action;
	}
}