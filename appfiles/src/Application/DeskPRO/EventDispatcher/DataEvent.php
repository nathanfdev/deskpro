<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage EmailGateway
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;

/**
 * A generic DataEvent class that just lets you pass data between the dispatcher and the listener.
 * For example, if a listener is designed to modify a variable, you can use this instead of writing
 * a totally new event class.
 */
class DataEvent extends Event
{
	/**
	 * @var array
	 */
	protected $data = array();

	public function __construct(array $data = array())
	{
		$this->data = $data;
	}


	/**
	 * @param  $name
	 * @return array
	 */
	public function __get($name)
	{
		return $this->data[$name];
	}


	/**
	 * @param  $name
	 * @param  $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}


	/**
	 * @param  $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->data[$name]);
	}


	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}
}