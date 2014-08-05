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
 * @category Controller
 */

namespace Application\DeskPRO\DBAL;

use Application\DeskPRO\App;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom loading database creds from config.php
 */
class ConnectionFactory extends \Doctrine\Bundle\DoctrineBundle\ConnectionFactory implements ContainerAwareInterface
{
	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	protected $container = null;

	public function __construct(array $typesConfig)
	{
		parent::__construct($typesConfig);

		if (!\Doctrine\DBAL\Types\Type::hasType('dpblob')) {
			\Doctrine\DBAL\Types\Type::addType('dpblob', 'Application\\DeskPRO\\DBAL\\Types\\DpBlobType');
		}
		if (!\Doctrine\DBAL\Types\Type::hasType('dpblob_file')) {
			\Doctrine\DBAL\Types\Type::addType('dpblob_file', 'Application\\DeskPRO\\DBAL\\Types\\DpBlobFileType');
		}
		if (!\Doctrine\DBAL\Types\Type::hasType('dp_json_obj')) {
			\Doctrine\DBAL\Types\Type::addType('dp_json_obj', 'Application\\DeskPRO\\DBAL\\Types\\DpJsonObject');
		}

		\Doctrine\DBAL\Types\Type::overrideType('array', 'Application\\DeskPRO\\DBAL\\Types\\DpArrayType');
		\Doctrine\DBAL\Types\Type::overrideType('object', 'Application\\DeskPRO\\DBAL\\Types\\DpObjectType');
	}

	public function setContainer(ContainerInterface $container = null)
	{
		$this->container = $container;
	}

	public function createConnection(array $params, Configuration $config = null, EventManager $eventManager = null, array $mappingTypes = array())
	{
		$params['wrapperClass'] = 'Application\\DeskPRO\\DBAL\\Connection';

		$host = $params['host'];
		$m = null;
		$dp_global_key = null;
		$recreate_retry = false;

		if (preg_match('#^from_user_config.(.*?)$#', $host, $m)) {
			$key = $m[1];
			$dp_global_key = $key;
			unset($params['host']);

			$conf = App::getConfig($key);
			if (!$conf && defined('DP_BUILDING')) {
				$conf = array('bogus'); // Dont need dbinfo
			}

			if (!$conf) {
				throw new \Exception("Invalid database key $key");
			}
			$params = array_merge($params, $conf);
			if (empty($params['driver'])) {
				$params['driver'] = 'pdo_mysql';
			}

			// When in testing mode, the db might be changed by overwriting a var
			if (defined('DP_BOOT_MODE') && DP_BOOT_MODE == 'testing' && !empty($GLOBALS['DP_TESTING_USEDB'])) {
				$params['dbname'] = $GLOBALS['DP_TESTING_USEDB'];
				$recreate_retry = true;

			// When testing a web request (eg selenium), there might exist a file that contains a different db name
			} else if (isset($GLOBALS['DP_USING_TESTING_CONFIG']) && $GLOBALS['DP_USING_TESTING_CONFIG'] && file_exists(DP_WEB_ROOT.'/testing_db_name')) {
				$params['dbname'] = trim(file_get_contents(DP_WEB_ROOT.'/testing_db_name'));
			}
		}

		// Sometimes in a pre-boot handler like serve_file.php we might
		// already have a connection, so use that PDO object
		if (isset($GLOBALS['DP_DEFAULT_CONNECTION_PDO']) && $host === 'from_user_config.db') {
			$params['pdo'] = $GLOBALS['DP_DEFAULT_CONNECTION_PDO'];
		}

		/** @var $conn \Doctrine\DBAL\Connection */
		$conn = parent::createConnection($params, $config, $eventManager, $mappingTypes);

		if ($recreate_retry) {
			try {
				$conn->connect();
			} catch (DBALException $err) {
				if (strpos($err->getMessage(), 'Unknown database') !== false) {
					$params_2 = $params;
					unset($params_2['dbname']);
					try {
						$conn2 = parent::createConnection($params_2);
						$conn2->exec("CREATE DATABASE `{$params['dbname']}`");

						$conn = parent::createConnection($params, $config, $eventManager, $mappingTypes);
						$conn->connect();
					} catch (\Exception $e) {
						error_log("Could not create test database: {$e->getMessage()}");
						throw $err;
					}
				} else {
					throw $err;
				}
			}
		}

		$conn->getDatabasePlatform()->registerDoctrineTypeMapping('BLOB', 'dpblob');

		if ($dp_global_key) {
			// Save ref in globals
			// This is an optimisation used by some logging that happens
			// outside of normal request/DI flow
			if (!isset($GLOBALS['DP_DB_CON'])) {
				$GLOBALS['DP_DB_CON'] = array();
			}
			$GLOBALS['DP_DB_CON'][$dp_global_key] = $conn;
		}

		return $conn;
	}
}
