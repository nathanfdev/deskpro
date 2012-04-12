<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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

namespace Orb\Auth\Adapter\Zend\Storage;

use \Symfony\Component\HttpFoundation\Session as SymfonySession;

/**
 * A storage adapter that uses a Symfony session wrapper
 */
class Session implements \Zend\Authentication\Storage
{
    /**
     * Object to proxy $_SESSION storage
     *
     * @var \Symfony\Component\HttpFoundation\Session
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
	public function __construct(SymfonySession $session, $name = 'OrbAuthAdapterZendStorageSfSession')
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
		return !$this->_session->has($this->_name);
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
