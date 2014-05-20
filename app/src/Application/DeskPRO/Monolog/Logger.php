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

namespace Application\DeskPRO\Monolog;

use Monolog\Handler\TestHandler;
use Monolog\Logger as BaseLogger;

class Logger extends BaseLogger
{
	/**
	 * @var TestHandler
	 */
	private $test_handler;


	/**
	 * @return bool
	 */
	public function hasEnabledSavedMessages()
	{
		return $this->test_handler ? true : false;
	}


	/**
	 * @return TestHandler
	 */
	private function _createTestHandler()
	{
		$test_handler = new TestHandler();
		return $test_handler;
	}


	/**
	 * Enables a local copy of all messages so you can easily fetch messages after
	 * (e.g., to save to a separate log after-the-fact).
	 *
	 * @throws \LogicException
	 */
	public function enableSavedMessages()
	{
		if ($this->test_handler) {
			throw new \LogicException("Saved messages has already been enabled.");
		}

		$this->test_handler = $this->_createTestHandler();
		$this->pushHandler($this->test_handler);
	}


	/**
	 * Clears any saved messages.
	 *
	 * @throws \LogicException
	 */
	public function clearSavedMessges()
	{
		if (!$this->test_handler) {
			throw new \LogicException("Saved messages has not been enabled.");
		}

		$k = array_search($this->test_handler, $this->handlers, true);
		$this->test_handler = $this->_createTestHandler();
		$this->handlers[$k] = $this->test_handler;
	}


	/**
	 * Gets a string of all the logged messages.
	 *
	 * @throws \LogicException
	 * @return string
	 */
	public function getSavedMessages()
	{
		if (!$this->test_handler) {
			throw new \LogicException("Saved messages has not been enabled.");
		}

		$log = array();
		foreach ($this->test_handler->getRecords() as $record) {
			if (!empty($record['formatted'])) {
				$log[] = $record['formatted'];
			} elseif (!empty($record['message'])) {
				$log[] = $record['message'];
			}
		}

		$log = trim(implode('', $log));

		return $log;
	}


	/**
	 * Gets an array of raw records.
	 *
	 * @return array
	 * @throws \LogicException
	 */
	public function getSavedMessagesRaw()
	{
		if (!$this->test_handler) {
			throw new \LogicException("Saved messages has not been enabled.");
		}

		return $this->test_handler->getRecords();
	}
}