<?php
/**
 * Orb
 *
 * @package Orb
 * @category Auth
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Auth\Adapter\Zend;

/**
 * A wrapper for ZF Auth adapters to make them work with the Orb system
 */
class ZendAdapter implements AdapterInterface
{
	/**
	 * The Zend adapter
	 * @var Zend\Authentication\Adapter
	 */
	protected $_adapter;


	
	/**
	 * @param \Zend\Authentication\Adapter $adapter
	 */
	public function __construct(\Zend\Authentication\Adapter $adapter)
	{
		$this->_adapter = $adapter;
	}


	public function authenticate()
	{
		$z_result = $this->_adapter->authenticate();

		if (!$z_result->isValid()) {
			$result = new \Orb\Auth\Result(\Orb\Auth\Result::FAILURE, null, array('zend_result' => $z_result));
			return $result;
		}

		$identity = new \Orb\Auth\Identity($z_result->getIdentity());
		$result = new \Orb\Auth\Result(\Orb\Auth\Result::SUCCESS, $identity, array('zend_result' => $z_result));

		return $result;
	}

	

	/**
	 * Get the adapter
	 * @return Zend\Authentication\Adapter
	 */
	public function getAdapter()
	{
		return $this->_adapter;
	}
}