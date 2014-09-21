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
 * Orb
 *
 * @package Orb
 * @category Logger
 */

namespace Orb\Logger\Handler;

use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

class ArrayHandler extends AbstractHandler
{
	/** @var int */
	protected $max_size = 5000;
	/** @var array */
	protected $messages = array();
	/** @var int */
	protected $count    = 0;

	public function __construct($max_size = 0, $level = Logger::DEBUG, $bubble = true)
	{
		parent::__construct($level, $bubble);
		$this->max_size = $max_size;
	}


	/**
	 * {@inheritdoc}
	 */
	public function handle(array $record)
	{
		if ($record['level'] < $this->level) {
			return false;
		}

		if ($this->max_size > 0 && $this->max_size === $this->count) {
			array_shift($this->messages);
			$this->count--;
		}

		if ($this->processors) {
			foreach ($this->processors as $processor) {
				$record = call_user_func($processor, $record);
			}
		}

		$this->messages[] = $this->getFormatter()->format($record);
		$this->count++;

		return false === $this->bubble;
	}


	/**
	 * Resets messages to empty
	 */
	public function reset()
	{
		$this->messages = array();
		$this->count = 0;
	}


	/**
	 * @return string[]
	 */
	public function getMessages()
	{
		return $this->messages;
	}



	/**
	 * @return string
	 */
	public function getMessagesAsString()
	{
		return implode("\n", $this->messages);
	}
}