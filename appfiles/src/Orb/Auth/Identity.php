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
class Identity implements \ArrayAccess
{
	/**
	 * A unique ID used by some service
	 * @var mixed
	 */
	protected $identity;

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
	 * Get the identitiy
	 *
	 * @return mixed
	 */
	public function getIdentity()
	{
		return $this->identity;
	}


	/**
	 * Get the real name of this person
	 *
	 * @return string
	 */
	public function getName()
	{
		return isset($this['name']) ? $this['name'] : false;
	}

	
	/**
	 * Get the display name or nickname of this person
	 *
	 * @return string
	 */
	public function getNickname()
	{
		if (isset($this['nickname'])) {
			return $this['nickname'];
		} elseif (isset($this['username'])) {
			return $this['username'];
		}

		return null;
	}


	
	/**
	 * Get an array of email addresses for this person. They should be sorted
	 * already in order of preference.
	 * 
	 * @return array
	 */
	public function getEmailAddresses()
	{
		if (isset($this['email_address'])) {
			return array($this['email_address']);
		} elseif (isset($this['email_addresses']) AND is_array($this['email_addresses'])) {
			return $this['email_addresses'];
		}

		return null;
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