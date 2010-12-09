<?php
/**
 * Orb
 *
 * @package Orb
 * @category Auth
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Auth\Adapter;

use \Orb\Auth\Adapter\SessionStateInterface;
use \Orb\Auth\Adapter\CallbackInterface;
use \Orb\Auth\StateHandler\StateHandlerInterface;
use \Orb\Auth\Result;


/**
 * A shell abstract adapter useful for all types that follow the two(or more)-step process of redirecting
 * the user offsite and back.
 */
abstract class AbstractCallbackAdatper implements AdapterInterface, SessionStateInterface, CallbackInterface
{
	/**
	 * If in callback context, then an array of callback data
	 * @var array
	 */
	protected $callback_data = null;

	/**
	 * State handler to store session data
	 * @var Orb\Auth\StateHandler\StateHandlerInterface;
	 */
	protected $state;

	/**
	 * The callback URL
	 * @var string
	 */
	protected $callback_url = null;

	/**
	 * Switches the adapter to the callback context using form data $data.
	 *
	 * @param array $data Form data or other callback data
	 * @return void
	 */
	public function setCallbackContext(array $data)
	{
		$this->callback_data = $data;
	}



	/**
	 * Set the URL the user is returned to
	 *
	 * @param string $url
	 */
	public function setCallbackUrl($url)
	{
		$this->callback_url = $url;
	}



	/**
	 * Get the callback URL
	 *
	 * @throws RuntimeException
	 * @return string
	 */
	public function getCallbackUrl()
	{
		if (!$this->callback_url) {
			throw new \RuntimeException('No callback URL was set');
		}
		return $this->callback_url;
	}



	/**
	 * Are we currently in callback mode?
	 *
	 * @return bool
	 */
	public function isCallbackMode()
	{
		return $this->callback_data !== null;
	}



	/**
	 * Authenticate a user.
	 *
	 * @return Orb\Auth\Result
	 */
	public function authenticate()
	{
		if ($this->isCallbackMode()) {
			return $this->authenticateCallback($this->callback_data, $this->getStateHandler());
		} else {
			return $this->authenticateInitialize($this->getStateHandler());
		}
	}



	/**
	 * Process the callback and return a final result.
	 *
	 * @return Orb\Auth\Result
	 */
	abstract protected function authenticateCallback(array $callback_data, StateHandlerInterface $state);



	/**
	 * Initialize the auth process by setting state, and returning a redirect result.
	 *
	 * @return Orb\Auth\Result
	 */
	abstract protected function authenticateInitialize(StateHandlerInterface $state);



	/**
	 * Set the state handler.
	 *
	 * @param Orb\Auth\StateHandler\StateHandlerInterface $state The state handler
	 * @return void
	 */
	public function setStateHandler(StateHandlerInterface $state)
	{
		$this->state = $state;
	}



	/**
	 * Get the state handler.
	 *
	 * @return Orb\Auth\StateHandler\StateHandlerInterface
	 */
	public function getStateHandler()
	{
		if (!$this->state) {
			throw new \RuntimeException('No state handler was set. Set one with setStateHandler');
		}
		return $this->state;
	}
}
