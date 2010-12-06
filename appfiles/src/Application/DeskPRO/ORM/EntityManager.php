<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category ORM
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\DeskPRO\ORM;

use \Symfony\Component\DependencyInjection\Container;

/**
 * We're subclassing the EntityManager because it's a good place to stick an entity factory for
 * when we need to create new instances of some entity, and a good place to pass in the container.
 *
 * TODO: This is no longer needed. Entities can use Application\DeskPRO\App to fetch required objects.
 * We should just start creating entities normally again and get rid of this.
 */
class EntityManager extends \Doctrine\ORM\EntityManager
{
	/**
	 * @var \Symfony\Component\DependencyInjection\Container
	 */
	protected $_container;


	
	/**
	 * Create a new instance of an entity.
	 *
	 * @param string $entityName The entity to create
	 * @param array $userParams Additional data to pass to the Entity's init method
	 */
	public function createEntity($entityName, array $userParams = array())
	{
		$metaData = $this->getClassMetadata($entityName);
		$className = $metaData->name;

		$obj = new $className($userParams);

		return $obj;
	}



	/**
	 * @return \Symfony\Component\DependencyInjection\Container
	 */
	public function getContainer()
	{
		return $this->_container;
	}



	protected function __construct(\Doctrine\DBAL\Connection $conn, \Doctrine\ORM\Configuration $config, \Doctrine\Common\EventManager $eventManager, Container $container = null)
	{
		parent::__construct($conn, $config, $eventManager);
		$this->_container = $container;

		// Set up event listener
		$this->getEventManager()->addEventSubscriber(new \Application\DeskPRO\ORM\EventListener\SetContainerListener($container));
	}


	
	public static function create($conn, \Doctrine\ORM\Configuration $config, \Doctrine\Common\EventManager $eventManager = null, Container $container = null)
	{
		if (!$config->getMetadataDriverImpl()) {
			throw \Doctrine\ORM\ORMException::missingMappingDriverImpl();
		}

		if (is_array($conn)) {
			$conn = \Doctrine\DBAL\DriverManager::getConnection($conn, $config, ($eventManager ?: new \Doctrine\Common\EventManager()));
		} else if ($conn instanceof \Doctrine\DBAL\Connection) {
			if ($eventManager !== null && $conn->getEventManager() !== $eventManager) {
				 throw \Doctrine\ORM\ORMException::mismatchedEventManager();
			}
		} else {
			throw new \InvalidArgumentException("Invalid argument: " . $conn);
		}

		return new EntityManager($conn, $config, $conn->getEventManager(), $container);
	}
}