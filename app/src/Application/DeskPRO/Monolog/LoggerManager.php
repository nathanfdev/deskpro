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

class LoggerManager
{
	/**
	 * @var Logger[]
	 */
	private $loggers = array();

	/**
	 * @var LoggerFactory
	 */
	private $factory;

	public function __construct(LoggerFactory $factory)
	{
		$this->factory = $factory;
	}


	/**
	 * @param string $id
	 * @param string|array $config
	 * @return Logger
	 */
	public function getLogger($id, $config = null)
	{
		if (isset($this->loggers[$id])) {
			return $this->loggers[$id];
		}

		if (!$config) {
			$config = array();
		}
		if (is_string($config)) {
			$config = array('@preset' => $config);
		}
		if (!isset($config['@preset'])) {
			$config['@preset'] = $id;
		}

		$this->loggers[$id] = $this->factory->createLoggerFromPreset($id, $config);

		return $this->loggers[$id];
	}


	/**
	 * @param string $id
	 * @param Logger $logger
	 * @throws \LogicException
	 */
	public function registerLogger($id, Logger $logger)
	{
		if (isset($this->loggers[$id])) {
			throw new \LogicException("$id is already registered");
		}

		$this->loggers[$id] = $logger;
	}


	/**
	 * @param string $id
	 * @return bool
	 */
	public function hasLogger($id)
	{
		return isset($this->loggers[$id]);
	}


	/**
	 * Unsets a registered logger.
	 *
	 * @param string $id
	 * @return void
	 */
	public function unsetLogger($id)
	{
		if (isset($this->loggers[$id])) {
			unset($this->loggers[$id]);
		}
	}


	/**
	 * Destroys a registered logger if it exists.
	 *
	 * Note that any classes have a reference to the logger will have a reference to
	 * an empty logger without any handlers or processors etc.
	 *
	 * @param string $id
	 * @return void
	 */
	public function destroyLogger($id)
	{
		if (!isset($this->loggers[$id])) {
			return;
		}

		$logger = $this->loggers[$id];
		unset($this->loggers[$id]);

		$this->destroyLoggerInstance($logger);
	}


	/**
	 * Unsets all processors handlers on a logger instance. For handlers that have a 'close'
	 * method, that method is called now (e.g., messages may be flushed).
	 *
	 * @param Logger $logger
	 */
	public function destroyLoggerInstance(Logger $logger)
	{
		try {
			while ($h = $logger->popHandler()) {
				if (method_exists($h, 'close')) {
					$h->close();
				}
			}
		} catch (\LogicException $e) {}

		try {
			while ($h = $logger->popProcessor()) { }
		} catch (\LogicException $e) {}
	}


	/**
	 * Create a new logger.
	 *
	 * @param string $channel
	 * @param string|array $config A preset name or an array of configuration
	 * @return Logger
	 */
	public function createLogger($channel, $config = null)
	{
		if (!$config) {
			$config = array();
		}
		if (is_string($config)) {
			$config = array('@preset' => $config);
		}

		$logger = $this->factory->createLoggerFromPreset($channel, $config);
		return $logger;
	}
}