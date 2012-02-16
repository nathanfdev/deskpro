<?php
/**
 * Orb
 *
 * @package Orb
 * @category Auth
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Auth;

/**
 * Holds the result from an auth try.
 *
 * Suggested common names:
 * - identity_friendly: A unique username to correspond with the ID
 * - name: For users full name
 * - email_address: For users email address
 * - nickname: The users nickname or displayname (if using usernames, probably that)
 */
class Identity implements \ArrayAccess
{
	/**
	 * A unique ID used by some service
	 * @var mixed
	 */
	protected $identity;

	/**
	 * A human-friendly identity. Still unique, but capable of changing (ie a username)
	 * @var string
	 */
	protected $friendly_identity;

	/**
	 * An array of raw userinfo
	 * @var array
	 */
	protected $raw_userinfo = array();

	/**
	 * @param mixed $identity
	 * @param array $raw_userinfo
	 */
	public function __construct($identity, array $raw_userinfo = array())
	{
		$this->identity = $identity;
		$this->raw_userinfo = $raw_userinfo;
	}

	

	/**
	 * Set the human friendly identity
	 * 
	 * @param string $friendly_identity 
	 */
	public function setFriendlyIdentity($friendly_identity)
	{
		$this->friendly_identity = $friendly_identity;
	}

	
	
	/**
	 * Get the identitiy
	 *
	 * @return mixed
	 */
	public function getIdentity()
	{
		return $this->identity;
	}



	/**
	 * Get the human friendly identity
	 *
	 * @return string
	 */
	public function getFriendlyIdentity()
	{
		return $this->friendly_identity;
	}



	/**
	 * Get the raw userdata returned with the auth record
	 *
	 * @return array
	 */
	public function getRawData()
	{
		return $this->raw_userinfo;
	}




	public function offsetExists($offset)
	{
		return isset($this->raw_userinfo[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->raw_userinfo[$offset];
	}

	public function offsetSet($offset, $value)
	{
		throw new \BadMethodCallException("Cannot set on the Identity object (tried to set `$offset`)");
	}

	public function offsetUnset($offset)
	{
		throw new \BadMethodCallException("Cannot unset on the Identity object (tried to unset `$offset`)");
	}
}