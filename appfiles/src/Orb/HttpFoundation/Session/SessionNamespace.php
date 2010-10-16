<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Usersources
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\HttpFoundation\Session;

/**
 * Is the same session interface, except we're sandboxed into a namespace.
 * This uses a Session directly.
 */
class SessionNamespace implements SessionInterface
{
	/**
	 * The session object being used
	 * @var Orb\HttpFoundation\Session\Session
	 */
	protected $session;

	/**
	 * The string key used in the above session to store these values
	 * @var string
	 */
	protected $namespace;

	protected function __construct(\Orb\HttpFoundation\Session\Session $session, $namespace)
	{
		$this->session = $session;
		$this->namespace = '__' . $namespace;
	}



	/**
	 * Starts the session storage.
	 */
	public function start()
	{
		$this->session->start();
	}



	/**
	 * Checks if a data item is defined.
	 *
	 * @param string $name The data item name
	 * @return boolean
	 */
	public function has($name)
	{
		$this->start();

		return isset($this->session->data[$this->namespace][$name]);
	}



	/**
	 * Returns a data item.
	 *
	 * @param string $name    The attribute name
	 * @param mixed  $default The default value
	 * @return mixed
	 */
	public function get($name, $default = null)
	{
		$this->start();

		return isset($this->session->data[$this->namespace][$name]) ? $this->session->data[$this->namespace][$name] : $default;
	}



	/**
	 * Sets a data item.
	 *
	 * @param string $name
	 * @param mixed  $value
	 */
	public function set($name, $value)
	{
		$this->start();

		if (isset($this->session->data[$this->namespace])) {
			$this->session->data[$this->namespace] = array();
		}

		$this->session->data[$this->namespace][$name] = $value;
	}



	/**
	 * Returns data.
	 *
	 * @return array
	 */
	public function getAllData()
	{
		$this->start();

		if (!isset($this->session->data[$this->namespace])) {
			return array();
		}

		return $this->session->data[$this->namespace];
	}



	/**
	 * Sets data.
	 *
	 * @param array $data Data
	 */
	public function setAllData(array $data)
	{
		$this->start();
		$this->session->data[$this->namespace][$name] = $data;
	}



	/**
	 * Removes a data item.
	 *
	 * @param string $name
	 */
	public function remove($name)
	{
		$this->start();
		if (!isset($this->session->data[$this->namespace])) return;

		unset($this->session->data[$this->namespace][$name]);

		// If its empty, just unset this namespace now
		if (!$this->session->data[$this->namespace]) {
			unset($this->session->data[$this->namespace]);
		}
	}



	/**
	 * Get this namespace name
	 *
	 * @return string
	 */
	public function getNamespaceName()
	{
		return substr($this->namespace, 2);
	}



	public function getIterator()
	{
		if (isset($this->session->data[$this->namespace])) {
			return \ArrayIterator($this->session->data[$this->namespace]);
		} else {
			return \ArrayIterator(array());
		}
	}

	public function offsetUnset($offset)
	{
		$this->remove($offset);
	}

	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}

	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	public function offsetExists($offset)
	{
		return $this->has($offset);
	}
}
