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
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class AbstractDefaultData
{
	/**
	 * Gets the priority. Lower numbers run first.
	 * This typically only makes sense for runInstall.
	 */
	const PRIORITY = 500;

	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private $container;

	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * @param DeskproContainer $container
	 * @param LoggerInterface $logger
	 */
	public function __construct(DeskproContainer $container, LoggerInterface $logger)
	{
		$this->container = $container;
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
	 * @return \Doctrine\ORM\EntityManager
	 */
	protected function getEm()
	{
		return $this->container->getEm();
	}


	/**
	 * @return \Application\DeskPRO\DBAL\Connection
	 */
	protected function getDb()
	{
		return $this->container->getDb();
	}


	/**
	 * @return DeskproContainer
	 */
	protected function getContainer()
	{
		return $this->container;
	}


	/**
	 * Called during a fresh install.
	 */
	public function runInstall()
	{

	}


	/**
	 * Called automatically during an upgrade when the system doesnt have the class installed.
	 * Usually the same as runInstall.
	 */
	public function runInstallViaUpgrade()
	{
		$this->runInstall();
	}


	/**
	 * Called automatically during upgrades when the package has been installed before.
	 * This is used to sync the database with any changes (e.g. adding new records or updating them).
	 */
	public function runSync()
	{

	}


	/**
	 * Called with the dp:reset-default-data command specifically. Usually the same as runSync.
	 */
	public function runReset()
	{
		$this->runSync();
	}
}