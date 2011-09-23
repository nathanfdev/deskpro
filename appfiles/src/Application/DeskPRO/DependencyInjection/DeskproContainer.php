<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category DependencyInjection
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\DependencyInjection;

use Symfony\Component\DependencyInjection\Container;

/**
 * This is an extension to the DI container that knows how to initialize
 * some services if they aren't registered and if they have a corresponding
 * factory in SystemServices.
 *
 * These services and factories use already registered services such as settings or database connections
 * to create themselves lazily.
 */
class DeskproContainer extends Container
{
	protected $system_services = array();

	/**
	 * This returns a reference to a system service.
	 *
	 * @throws \InvalidArgumentException
	 * @param string $id
	 * @return mixed
	 */
	public function getSystemService($id)
	{
		if (isset($this->system_services[$id])) {
			$this->system_services[$id] = $id;
		}

		$classname = 'Application\\DeskPRO\\DependencyInjection\\SystemServices\\' . $this->camelize($id) . 'Service';

		if (!class_exists($classname)) {
			throw new \InvalidArgumentException("Invalid service `$id`");
		}

		$obj = $classname::create($this);

		$this->system_services[$id] = $obj;

		return $obj;
	}


	/**
	 * This calls a system factory and returns a new instance of some kind of object.
	 *
	 * @throws \InvalidArgumentException
	 * @param string $id
	 * @return mixed
	 */
	public function getSystemObject($id, array $options = array())
	{
		$classname = 'Application\\DeskPRO\\DependencyInjection\\SystemServices\\' . $this->camelize($id) . 'Factory';

		if (!class_exists($classname)) {
			throw new \InvalidArgumentException("Invalid factory `$id`");
		}

		$obj = $classname::create($this, $options);
		return $obj;
	}
}
