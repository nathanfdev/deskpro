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
 * @category Entities
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Monolog\Logger;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Person;
use Orb\Util\OptionsArray;

class ExecutorContext implements PersonContextInterface
{
	/**
	 * @var \Orb\Util\OptionsArray
	 */
	private $vars;

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
	 * @var null
	 */
	private $event_performer = 'system';

	/**
	 * @var \Monolog\Logger
	 */
	private $logger;


	public function __construct(Logger $logger)
	{
		$this->vars = new OptionsArray();
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
	 * @return OptionsArray
	 */
	public function getVars()
	{
		return $this->vars;
	}


	/**
	 * @param $event_type
	 */
	public function setEventType($event_type)
	{
		$this->event_type = $event_type;
	}


	/**
	 * @return string
	 */
	public function getEventType()
	{
		return $this->event_type;
	}


	/**
	 * @param string $event_method
	 */
	public function setEventMethod($event_method)
	{
		$this->event_method = $event_method;
	}


	/**
	 * @return string
	 */
	public function getEventMethod()
	{
		return $this->event_method;
	}


	/**
	 * @param string $event_performer
	 */
	public function setEventPerformer($event_performer)
	{
		$this->event_performer = $event_performer;
	}


	/**
	 * @return string
	 */
	public function getEventPerformer()
	{
		return $this->event_performer;
	}
}