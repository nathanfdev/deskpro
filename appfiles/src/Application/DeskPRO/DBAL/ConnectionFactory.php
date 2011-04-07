<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\DBAL;

use \Application\DeskPRO\App;

use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Doctrine\Common\EventManager;
use \Doctrine\DBAL\Configuration;
use \Doctrine\DBAL\DriverManager;
use \Doctrine\DBAL\Types\Type;

/**
 * Custom loading database creds from config.php
 */
class ConnectionFactory extends \Symfony\Bundle\DoctrineBundle\ConnectionFactory
{
	public function createConnection(array $params, Configuration $config = null, EventManager $eventManager = null)
	{
		$params['wrapperClass'] = 'Application\\DeskPRO\\DBAL\\Connection';

		$host = $params['host'];
		$m = null;
		if (preg_match('#^from_user_config.(.*?)$#', $host, $m)) {
			$key = $m[1];
			unset($params['host']);

			$params = array_merge($params, App::getConfig($key));
		}

		$conn = parent::createConnection($params, $config, $eventManager);

		$evm = $conn->getEventManager();
		$evm->addEventSubscriber(new \Gedmo\Tree\TreeListener());

		return $conn;
	}
}