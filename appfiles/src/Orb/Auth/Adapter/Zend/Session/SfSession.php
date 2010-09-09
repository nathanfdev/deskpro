<?php
/**
 * Orb
 *
 * @package Orb
 * @category Auth
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Auth\Adapter\Zend\Storage;

use \Symfony\Component\HttpFoundation\Session;

/**
 * A storage adapter that uses a Symfony session wrapper
 */
class Session implements \Zend\Authentication\Storage
{
    /**
     * Object to proxy $_SESSION storage
     *
     * @var Symfony\Component\HttpFoundation\Session
     */
    protected $_session;
	
	/**
	 * The name (array key) in the session
	 * @var string
	 */
	protected $_name;



	/**
	 * @param Session $session
	 * @param string $name The name in the session to save data to
	 */
	public function __construct(Session $session, $name = 'OrbAuthAdapterZendStorageSfSession')
	{
		$this->_session = $session;

		$this->_name= $name;
	}

	

    /**
     * Returns the session namespace
     *
     * @return string
     */
    public function getSessionDataName()
    {
        return $this->_name;
    }



    /**
     * @return boolean
     */
    public function isEmpty()
    {
		return !isset($this->_session->has($this->_name));
    }



    /**
     * @return mixed
     */
    public function read()
    {
		return $this->_session->get($this->_name);
    }



    /**
     * @param  mixed $contents
     * @return void
     */
    public function write($contents)
    {
		$this->_session->put($this->_name, $contents);
    }



    /**
     * @return void
     */
    public function clear()
    {
		$this->_session->remove($this->_name);
    }
}
