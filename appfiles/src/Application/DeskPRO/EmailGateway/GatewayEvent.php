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

namespace Application\DeskPRO\EmailGateway;

use Symfony\Component\EventDispatcher\Event;

class GatewayEvent extends Event
{
	/**
	 * @var \Application\DeskPRO\EmailGateway\AbstractGatewayProcessor
	 */
	protected $gateway;

	/**
	 * @var array
	 */
	protected $data = array();

	public function __construct(AbstractGatewayProcessor $gateway, array $data = array())
	{
		$this->gateway = $gateway;
		$this->data = $data;
	}


	/**
	 * @var \Application\DeskPRO\EmailGateway\AbstractGatewayProcessor
	 */
	public function getGateway()
	{
		return $this->gateway;
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
