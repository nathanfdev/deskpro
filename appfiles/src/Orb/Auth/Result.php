<?php
/**
 * Orb
 *
 * @package Orb
 * @category Auth
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Auth;

/**
 * Holds the result from an auth try.
 */
class Result implements \ArrayAccess
{
	const FAILURE_EXCEPTION = -1;
	const FAILURE = 0;
	const SUCCESS = 1;
	const REQUIRES_REDIRECT = 2;

	/**
	 * Array of reasons for failure.
	 * @var array
	 */
	protected $_messages = array();

	/**
	 * The identity returned if the auth was success.
	 *
	 * @return Orb\Auth\Identity
	 */
	protected $_identity = null;

	/**
	 * Result code from the ogin attempt
	 * @var int
	 */
	protected $_code = 0;


	/**
	 * If $code is Result::REQUIRES_REDIRECT then $messages should have an item called
	 * 'redirect_url'.
	 *
	 * @param int $code Success (Result::SUCCESS) or error code on failure
	 * @param \Orb\Auth\Identity $identity If successful, the user identity
	 * @param array $messages Messages of why the login failed
	 */
	public function __construct($code, \Orb\Auth\Identity $identity = null, array $messages = array())
	{
		$this->_code = $code;
		$this->_identity;
		$this->_messages = $messages;
	}



	/**
	 * Was the login valid?
	 *
	 * @return bool
	 */
	public function isValid()
	{
		return $this->code == self::SUCCESS;
	}

	

	/**
	 * Does the user need to be redirected to finish authentication?
	 *
	 * @return bool
	 */
	public function isRedirectRequired()
	{
		return $this->code == self::REQUIRES_REDIRECT;
	}


	
	/**
	 * If the result says the user must be redirect, get the URL to redirect the user to.
	 *
	 * @return string
	 */
	public function getRedirectUrl()
	{
		if (!$this->isRedirectRequired() OR !isset($this->_messages['redirect_url'])) {
			throw new \UnexpectedValueException('The result does not specify redirection');
		}

		return $this->_messages['redirect_url'];
	}


	
	/**
	 * Get the identity.
	 * 
	 * @return \Orb\Auth\Identity
	 */
	public function getIdentity()
	{
		if ($this->_identity === null) {
			throw new \UnexpectedValueException('No identity was set, the login failred');
		}

		return $this->_identity;
	}



	/**
	 * Get messages (reasons for login failure)/
	 * 
	 * @return array
	 */
	public function getMessages()
	{
		return $this->_messages;
	}
}