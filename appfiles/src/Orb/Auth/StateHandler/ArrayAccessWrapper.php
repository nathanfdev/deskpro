<?php
/**
 * Orb
 *
 * @package Orb
 * @category Auth
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Auth\StateHandler;

/**
 * A simple state handler wrapper that seamlessly wraps any ArrayAccess object.
 * Since most session handlers offer ArrayAccess, this is the easiest way to provide
 * a state handler.
 *
 * By default the clearState method will iterate through the object and delete all
 * keys. If other behavior is required, then you can set a custom method or callback
 * using the setClearStateMethod() method. Or you might want to simply sub-class
 * this class for a specific session handler.
 */
class ArrayAccessWrapper implements StateHandlerInterface
{
	/**
	 * The array-like state object
	 * @var ArrayAccess
	 */
	protected $_state_obj;

	/**
	 * The method on the state object that we can call to clear state.
	 * @var string
	 */
	protected $_clear_state_method = null;


	/**
	 * @param $state_obj The array-like object that can save state
	 */
	public function __construct(\ArrayAccess $state_obj)
	{
		$this->_state_obj = $state_obj;
	}



	/**
	 * If the object has it's own clear method, then you can set it's method name
	 * here that will be called with clearState().
	 *
	 * Optionally $method can be a callback
	 *
	 * @param string $method The name of the method on the state object to call when clearing state
	 * @return void
	 */
	public function setClearStateMethod($method)
	{
		if (is_string($method)) {
			if (!method_exists($this->_state_obj, $method)) {
				throw new \InvalidArgumentException("The state object has no method called `$method`");
			}
		} else {
			if (!is_callable($method)) {
				throw new \InvalidArgumentException("The parameter passed is not a valid callback");
			}
		}

		$this->_clear_state_method = $method;
	}



	/**
	 * Clears all state data, or resets back into its initial state.
	 *
	 * @return void
	 */
	public function clearState()
	{
		// See if we know how to clear state on the object
		if ($this->_clear_state_method !== null) {
			if (is_string($this->_clear_state_method)) {
				$method = $this->_clear_state_method;
				$this->_state_obj->$method();
			} else {
				call_user_func($this->_clear_state_method, $this->_state_obj);
			}

		// Otherwise if its traversable we can just clear each key
		} elseif ($this->_state_obj instanceof \Traversable) {
			foreach ($this->_state_obj as $k => $v) {
				unset($this->_state_obj[$k]);
			}

		// Uh oh, I have no idea how
		} else {
			throw new \RuntimeException('No clearState() method was registered; I don\'t know how to clearState().');
		}
	}



	public function offsetUnset($offset)
	{
		unset($this->_state_obj[$offset]);
	}

	public function offsetSet($offset, $value)
	{
		$this->_state_obj[$offset] = $value;
	}

	public function offsetGet($offset)
	{
		return $this->_state_obj[$offset];
	}

	public function offsetExists($offset)
	{
		return isset($this->_state_obj[$offset]);
	}
}
