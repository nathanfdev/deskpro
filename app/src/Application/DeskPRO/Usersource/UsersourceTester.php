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

namespace Application\DeskPRO\Usersource;

use Application\DeskPRO\Entity\Usersource;
use Orb\Auth\Adapter\AdapterInterface;
use Orb\Log\Logger;
use Orb\Log\Writer\ArrayWriter;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class UsersourceTester
{
	/**
	 * @var Adapter\AbstractAdapter
	 */
	private $adapter;

	/**
	 * @var bool
	 */
	private $has_run = false;

	/**
	 * @var bool
	 */
	private $is_valid;

	/**
	 * @var string
	 */
	private $log;

	/**
	 * @var array
	 */
	private $raw_data;


	/**
	 * @param string $type
	 * @param array $settings
	 * @return UsersourceTester
	 */
	public static function createFromOptions($type, array $settings)
	{
		$us = new Usersource();
		$us->source_type = $type;
		$us->setOptions($settings);
		return new self($us->getAdapter()->getAuthAdapter());
	}


	/**
	 * @param Usersource $usersource
	 * @return UsersourceTester
	 */
	public static function createFromUsersource(Usersource $usersource)
	{
		return new self($usersource->getAdapter()->getAuthAdapter());
	}


	/**
	 * @param AdapterInterface $adapter
	 */
	public function __construct(AdapterInterface $adapter)
	{
		$this->adapter = $adapter;
	}


	/**
	 * @param string $username
	 * @param string $password
	 * @return bool
	 * @throws \RuntimeException
	 */
	public function test($username, $password)
	{
		if ($this->has_run) {
			throw new \RuntimeException("Test has already been run");
		}

		$this->has_run = true;
		$adapter = $this->adapter;

		$logger = new Logger();
		$arr_wr = new ArrayWriter();
		$logger->addWriter($arr_wr);

		$logger->logDebug("Adapter: " . get_class($adapter));
		$logger->logDebug("Test: $username :: $password");
		$logger->logDebug("--- Begin ---");
		$start = microtime(true);

		if (method_exists($adapter, 'setLogger')) {
			$adapter->setLogger($logger);
		}

		$adapter->setFormData(array(
			'username' => $username,
			'password' => $password
		));
		$result = $adapter->authenticate();

		$time = microtime(true) - $start;
		$logger->logDebug("--- Done (".sprintf('%.4f', $time)."s) ---");

		$log = implode("\n", $arr_wr->getMessages());

		if ($result && $result->isValid() && $result->getIdentity()) {
			$result_raw = "DATA RECORD:\n=======================================================\n";
			$raw_data = Arrays::mapRecursive($result->getIdentity()->getRawData(), function($v) {
				if (is_int($v) || is_float($v) || ctype_digit($v) || is_bool($v) || is_null($v) || ctype_print($v)) {
					return $v;
				} else {
					return Strings::utf8_bad_strip($v);
				}
			});
			$result_raw .= print_r($raw_data, true);
		} else {
			$result_raw = "No Identity";
		}

		$clean = $result_raw;
		if (!$clean) {
			$result_raw = Strings::utf8_bad_strip($result_raw);
			$clean = @htmlspecialchars($result_raw, \ENT_QUOTES, 'ISO-8895-1');
			if (!$clean) {
				$clean = "[Data contains invalid characters]";
			}
		}

		$result_raw = $clean;

		$this->log = $log;
		$this->raw_data = $result_raw;
		$this->is_valid = $result->isValid();

		return $this->is_valid;
	}


	/**
	 * @return string
	 * @throws \RuntimeException
	 */
	public function getLog()
	{
		if (!$this->has_run) {
			throw new \RuntimeException("You must run test() first");
		}

		return $this->log;
	}


	/**
	 * @return array
	 * @throws \RuntimeException
	 */
	public function getRawData()
	{
		if (!$this->has_run) {
			throw new \RuntimeException("You must run test() first");
		}

		return $this->raw_data;
	}


	/**
	 * @return bool
	 * @throws \RuntimeException
	 */
	public function isValid()
	{
		if (!$this->has_run) {
			throw new \RuntimeException("You must run test() first");
		}

		return $this->is_valid;
	}
}