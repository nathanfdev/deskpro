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

namespace Application\DeskPRO\EmailGateway\FetcherStorage;

use Application\DeskPRO\EmailGateway\Storage\Pop3;
use Orb\Log\Logger;

class Pop3Storage implements FetcherStorageInterface
{
	/**
	 * @var string
	 */
	private $host;

	/**
	 * @var int
	 */
	private $port;

	/**
	 * @var string|null
	 */
	private $user;

	/**
	 * @var string|null
	 */
	private $password;

	/**
	 * @var string|null
	 */
	private $secure;

	/**
	 * @var \Application\DeskPRO\EmailGateway\Storage\Pop3
	 */
	private $storage = null;

	/**
	 * @var
	 */
	private $logger;


	/**
	 * @param string $host
	 * @param string $port
	 * @param string|null $user
	 * @param string|null $password
	 * @param string|null $secure
	 */
	public function __construct($host, $port, $user, $password, $secure = null)
	{
		$this->host = $host;
		$this->port = $port;

		if ($user !== null) {
			$this->user = $user;
		}
		if ($password !== null) {
			$this->password = $password;
		}

		if ($secure == 'ssl') {
			$this->secure = 'ssl';
		} elseif ($secure == 'tls') {
			$this->secure = 'tls';
		}
	}


	/**
	 * @return \Application\DeskPRO\EmailGateway\Storage\Pop3
	 */
	public function getStorage()
	{
		if ($this->storage !== null) {
			return $this->storage;
		}

		$options = array();
		$options['host']     = $this->host;
		$options['port']     = $this->port;
		$options['user']     = $this->user ?: '';
		$options['password'] = $this->password ?: '';
		$options['ssl']      = $this->secure;

		if ($this->logger) {
			$options['logger'] = $this->logger;
		}

		$storage = new Pop3($options);
		return $storage;
	}


	/**
	 * {@inheritDoc}
	 */
	public function closeStorage()
	{
		if ($this->storage !== null) {
			$this->storage->close();
			$this->storage = null;
		}
	}


	/**
	 * {@inheritDoc}
	 */
	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getLogger()
	{
		return $this->logger;
	}
}