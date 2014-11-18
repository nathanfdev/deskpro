<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * Orb
 *
 * @package Orb
 * @category Auth
 */

namespace Orb\Auth\StateHandler;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * A statehandler that wraps a session to provide its own SessionBagInterface (named by the prefix) to work with Auth system
 */
class SessionWrapper implements StateHandlerInterface
{
	/**
	 * @var Session
	 */
	protected $session;

	/**
	 * The method on the state object that we can call to clear state.
	 * @var string
	 */
	protected $_clear_state_method = null;

	/**
	 * A prefix to prefix all keys with
	 * @var string
	 */
	protected $_prefix;

	/**
	 * @param Session $state_obj the symfony session object
	 */
	public function __construct(Session $state_obj, $prefix = 'state_obj')
	{
		$this->session = $state_obj;
        $this->setPrefix($prefix);
	}


	/**
	 * Set the key prefix
	 *
	 * @param string $prefix
	 * @return void
	 */
	public function setPrefix($prefix)
	{
		$this->_prefix = $prefix;
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
        // we know how to clear the session storage
	}


	/**
	 * Clears all state data, or resets back into its initial state.
	 *
	 * @return void
	 */
	public function clearState()
	{
        $this->getSessionBag()->clear();
	}


	public function offsetUnset($offset)
	{
		$this->getSessionBag()->remove($this->addPrefix($offset));
	}

	public function offsetSet($offset, $value)
	{
        $this->getSessionBag()->set($this->addPrefix($offset), $value);
	}

	public function offsetGet($offset)
	{
		return $this->getSessionBag()->get($this->addPrefix($offset));
	}

	public function offsetExists($offset)
	{
		return $this->getSessionBag()->has($this->addPrefix($offset));
	}

    protected function addPrefix($name)
    {
        return $this->_prefix.$name;
    }

    /**
     * @return string
     */
    protected function bagName()
    {
        return 'attributes';
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface
     */
    protected function getSessionBag()
    {
        return $this->session->getBag($this->bagName());
    }
}
