<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\Person;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Orb\Util\OptionsArray;

class ExecutorContext implements ExecutorContextInterface
{
	const EVENT_NEW = 'newticket';
	const EVENT_REPLY = 'newreply';
	const EVENT_UPDATE = 'update';
	const EVENT_NOOP = 'noop';

	const METHOD_API = 'api';
	const METHOD_WEB = 'web';
	const METHOD_EMAIL = 'email';
	const METHOD_SMS = 'sms';

	/**
	 * @var \Orb\Util\OptionsArray
	 */
	private $vars;

	/**
	 * @var \Orb\Util\OptionsArray
	 */
	private $user_vars;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	private $person_context;

	/**
	 * @var string
	 */
	private $event_type = 'update';

	/**
	 * @var string
	 */
	private $event_method = 'system';

	/**
	 * @var array
	 */
	private $event_method_options = array();

	/**
	 * @var null
	 */
	private $event_performer = 'system';

	/**
	 * @var \Monolog\Logger
	 */
	private $logger;

	public function __construct(Logger $logger = null)
	{
		if (!$logger) {
			// Null logger
			$logger = new Logger('ticket', array(new NullHandler()));
		}

		$this->vars = new OptionsArray();
		$this->user_vars = new OptionsArray();
		$this->logger = $logger;
	}


	/**
	 * @return Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}


	/**
	 * @param Person $person
	 * @param bool   $set_performer  Automatically set the event performer based on this user
	 */
	public function setPersonContext(Person $person, $set_performer = true)
	{
		$this->person_context = $person;

		if ($set_performer) {
			if ($this->person_context->is_agent) {
				$this->event_performer = 'agent';
			} else {
				$this->event_performer = 'user';
			}
		}
	}


	/**
	 * @return Person
	 */
	public function getPersonContext()
	{
		return $this->person_context;
	}


	/**
	 * @return OptionsArray
	 */
	public function getVars()
	{
		return $this->vars;
	}


	/**
	 * @return OptionsArray
	 */
	public function getUserVars()
	{
		return $this->user_vars;
	}


	/**
	 * @return bool
	 */
	public function hasEmailContext()
	{
		return $this->vars->has('email_reader');
	}


	/**
	 * @param AbstractReader $reader
	 */
	public function setEmailContext(AbstractReader $reader)
	{
		$this->vars->set('email_reader', $reader);
	}


	/**
	 * @return AbstractReader
	 * @throws \RuntimeException
	 */
	public function getEmailContext()
	{
		if (!$this->vars->has('email_reader')) {
			throw new \RuntimeException("No email reader has been set");
		}

		return $this->vars->get('email_reader');
	}


	/**
	 * The event type. This indicates what kind of triggers are fired as well.
	 *
	 * Possible values:
	 * - newticket
	 * - newreply
	 * - update
	 *
	 * @param string $event_type
	 */
	public function setEventType($event_type)
	{
		$this->event_type = $event_type;
	}


	/**
	 * @return string newticket, newreply, update
	 */
	public function getEventType()
	{
		return $this->event_type;
	}


	/**
	 * The event method is how the event is being fired.
	 *
	 * @param string $event_method          Event method (email, api, or web)
	 * @param array  $event_method_options  Event options (eg a URL etc)
	 */
	public function setEventMethod($event_method, array $event_method_options = array())
	{
		$this->event_method = $event_method;
		$this->event_method_options = $event_method_options;
	}


	/**
	 * @return string email, api or web
	 */
	public function getEventMethod()
	{
		return $this->event_method;
	}


	/**
	 * @return array
	 */
	public function getEventMethodOptions()
	{
		return $this->event_method_options;
	}


	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getEventMethodOption($name)
	{
		return isset($this->event_method_options[$name]) ? $this->event_method_options[$name] : null;
	}


	/**
	 * @param string $event_performer
	 */
	public function setEventPerformer($event_performer)
	{
		$this->event_performer = $event_performer;
	}


	/**
	 * @return string system, user or agent
	 */
	public function getEventPerformer()
	{
		return $this->event_performer;
	}
}
