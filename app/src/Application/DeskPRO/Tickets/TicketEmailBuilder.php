<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
 * @category Entities
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\CustomFields\PersonFieldManager;
use Application\DeskPRO\CustomFields\TicketFieldManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Mail\Mailer;
use Application\DeskPRO\Settings\Settings;
use Application\DeskPRO\TicketLayout\TicketLayoutManager;
use Application\DeskPRO\Translate\Translate;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Orb\Util\OptionsArray;

class TicketEmailBuilder
{
	/**
	 * @var \Orb\Util\OptionsArray
	 */
	private $options;

	private function __construct()
	{
		$this->options = new OptionsArray();
	}

	/**
	 * @return TicketEmailBuilder
	 */
	public static function create()
	{
		return new self();
	}

	/**
	 * @param DeskproContainer $container
	 * @return TicketEmailBuilder
	 */
	public static function createFromContainer(DeskproContainer $container)
	{
		$build = new self();
		$build->setEm($container->getEm())
			->setSettings($container->getSettingsHandler())
			->setMailer($container->getMailer())
			->setTranslate($container->getTranslator())
			->setTicketFieldManager($container->getTicketFieldManager())
			->setUserFieldManager($container->getPersonFieldManager())
			->setTicketLayoutManager($container->getTicketLayoutManager());

		return $build;
	}

	/**
	 * @return TicketEmail
	 */
	public function buildTicketEmail()
	{
		return new TicketEmail($this->options->all());
	}

	/**
	 * @param Settings $settings
	 * @return $this
	 */
	public function setSettings(Settings $settings)
	{
		$this->options->set('settings', $settings);
		return $this;
	}

	/**
	 * @param Mailer $mailer
	 * @return TicketEmailBuilder
	 */
	public function setMailer(Mailer $mailer)
	{
		$this->options->set('mailer', $mailer);
		return $this;
	}

	/**
	 * @param Translate $tr
	 * @return TicketEmailBuilder
	 */
	public function setTranslate(Translate $tr)
	{
		$this->options->set('translate', $tr);
		return $this;
	}

	/**
	 * @param EntityManager $em
	 * @return TicketEmailBuilder
	 */
	public function setEm(EntityManager $em)
	{
		$this->options->set('em', $em);
		return $this;
	}

	/**
	 * @param TicketFieldManager $field_manager
	 * @return TicketEmailBuilder
	 */
	public function setTicketFieldManager(TicketFieldManager $field_manager)
	{
		$this->options->set('ticket_field_manager', $field_manager);
		return $this;
	}

	/**
	 * @param PersonFieldManager $field_manager
	 * @return TicketEmailBuilder
	 */
	public function setUserFieldManager(PersonFieldManager $field_manager)
	{
		$this->options->set('user_field_manager', $field_manager);
		return $this;
	}

	/**
	 * @param TicketLayoutManager $ticket_layout_manager
	 * @return TicketEmailBuilder
	 */
	public function setTicketLayoutManager(TicketLayoutManager $ticket_layout_manager)
	{
		$this->options->set('ticket_layout_manager', $ticket_layout_manager);
		return $this;
	}

	/**
	 * @param Ticket $ticket
	 * @return TicketEmailBuilder
	 */
	public function setTicket(Ticket $ticket)
	{
		$this->options->set('ticket', $ticket);
		return $this;
	}

	/**
	 * @param Person $person
	 * @return TicketEmailBuilder
	 */
	public function setToPerson(Person $person)
	{
		$this->options->set('to_person', $person);
		return $this;
	}

	/**
	 * @param Person[] $people
	 * @return $this
	 */
	public function setToPeople(array $people)
	{
		throw new \RuntimeException();
	}

	/**
	 * What type of user the email is intended for. This is a safety feature.
	 * For examlpe, if it's an agent email but the user isn't an agent, we can catch
	 * an error.
	 *
	 * @return TicketEmailBuilder
	 */
	public function setUserMode()
	{
		$this->options->set('user_mode', 'user');
		return $this;
	}

	/**
	 * @see setUserMode
	 * @return TicketEmailBuilder
	 */
	public function setAgentMode()
	{
		$this->options->set('user_mode', 'agent');
		return $this;
	}

	/**
	 * @param string $template_name
	 * @return TicketEmailBuilder
	 */
	public function setTemplateName($template_name)
	{
		$this->options->set('template_name', $template_name);
		return $this;
	}

	/**
	 * @param $from_name
	 * @return TicketEmailBuilder
	 */
	public function setFromName($from_name)
	{
		$this->options->set('from_name', $from_name);
		return $this;
	}

	/**
	 * @param EmailAccount $account
	 * @return TicketEmailBuilder
	 */
	public function setFromEmailAccount(EmailAccount $account = null)
	{
		$this->options->set('from_email_account', $account);
		return $this;
	}

	/**
	 * On user emails, this will CC in other user participants on the email.
	 *
	 * @return TicketEmailBuilder
	 */
	public function enableUserCc()
	{
		$this->options->set('cc_users', true);
		return $this;
	}

	/**
	 * @see enableUserCc
	 * @return TicketEmailBuilder
	 */
	public function disableUserCc()
	{
		$this->options->set('cc_users', false);
	}

	/**
	 * If this is an automatic email (e.g., auto-reply), then this will add 'auto' headers
	 * to the email. These special headers prevent other automated systems from sending their
	 * own auto-replies.
	 *
	 * @return TicketEmailBuilder
	 */
	public function setIsAuto()
	{
		$this->options->set('is_auto', true);
		return $this;
	}

	/**
	 * @see setIsAuto
	 * @return TicketEmailBuilder
	 */
	public function setIsNotAuto()
	{
		$this->options->set('is_auto', true);
		return $this;
	}


	/**
	 * Sets the maximum size of attachments that will be sent with the message.
	 *
	 * @param int $size
	 * @return TicketEmailBuilder
	 */
	public function setMaxAttachSize($size)
	{
		$this->options->set('max_attach_size', (int)$size);
		return $this;
	}

	/**
	 * @param Logger $logger
	 * @return TicketEmailBuilder
	 */
	public function setLogger(Logger $logger)
	{
		$this->options->set('logger', $logger);
		return $this;
	}

	/**
	 * @param array $headers
	 * @return TicketEmailBuilder
	 */
	public function setHeaders($headers = array())
	{
		$this->options->set('headers', $headers);
		return $this;
	}

	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options->all();
	}
}