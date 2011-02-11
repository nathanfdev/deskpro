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
		if (isset($params['driverOptions']['from_user_config'])) {
			$key = $params['driverOptions']['from_user_config'];
			unset($params['driverOptions']['from_user_config']);

			$params = array_merge($params, App::getConfig($key));
		}

		return parent::createConnection($params, $config, $eventManager);
	}
}