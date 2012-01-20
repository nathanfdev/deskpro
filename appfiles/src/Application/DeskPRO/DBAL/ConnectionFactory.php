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

use Application\DeskPRO\App;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;

/**
 * Custom loading database creds from config.php
 */
class ConnectionFactory extends \Symfony\Bundle\DoctrineBundle\ConnectionFactory implements ContainerAwareInterface
{
	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	protected $container = null;

	public function setContainer(ContainerInterface $container = null)
	{
		$this->container = $container;
	}

	public function createConnection(array $params, Configuration $config = null, EventManager $eventManager = null, array $mappingTypes = array())
	{
		$params['wrapperClass'] = 'Application\\DeskPRO\\DBAL\\Connection';

		$host = $params['host'];
		$m = null;
		if (preg_match('#^from_user_config.(.*?)$#', $host, $m)) {
			$key = $m[1];
			unset($params['host']);

			$conf = App::getConfig($key);
			if (!$conf) {
				throw new \Exception("Invalid database key $key");
			}
			$params = array_merge($params, $conf);
			if (empty($params['driver'])) {
				$params['driver'] = 'pdo_mysql';
			}
		}

		$conn = parent::createConnection($params, $config, $eventManager, $mappingTypes);

		$evm = $conn->getEventManager();

		if ($this->container && $this->container->has('event_dispatcher')) {
			$evm->addEventSubscriber(new SymfonyEventConnector($this->container->get('event_dispatcher')));
		}

		return $conn;
	}
}
