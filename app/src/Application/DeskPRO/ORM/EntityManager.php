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
 * @subpackage
 */

namespace Application\DeskPRO\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Configuration;
use Doctrine\Common\EventManager;
use Application\DeskPRO\ORM\UnitOfWork;
use Application\DeskPRO\ORM\Proxy\ProxyFactory;
use Application\DeskPRO\ORM\Unprivate\UnprivateEntityManager;

/**
 * Customized EM to override proxy factory
 */
class EntityManager extends UnprivateEntityManager
{
	protected function __construct(Connection $conn, Configuration $config, EventManager $eventManager)
	{
		parent::__construct($conn, $config, $eventManager);

		$this->unitOfWork = new UnitOfWork($this);
		$this->proxyFactory = new ProxyFactory(
			$this,
			$config->getProxyDir(),
			$config->getProxyNamespace(),
			$config->getAutoGenerateProxyClasses()
		);
	}

	public function persist($entity)
	{
		if (dp_get_config('debug.em_persist_log')) {
			static $logger = null;

			if ($logger === null) {
				$logger = new \Orb\Log\Logger();
				$wr = new \Orb\Log\Writer\Stream(dp_get_log_dir() . '/em-persist.log', 'a');
				$logger->addWriter($wr);
			}

			$type  = get_class($entity);
			$id    = isset($entity['id']) ? $entity['id'] : '0';
			$trace = \DeskPRO\Kernel\KernelErrorHandler::formatBacktrace(debug_backtrace());
			$logger->logDebug("Persist: $type :: $id\n$trace\n\n");
		}

		parent::persist($entity);
	}
}
