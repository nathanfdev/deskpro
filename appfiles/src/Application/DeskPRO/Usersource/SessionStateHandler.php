<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Usersource
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Usersource;

use Application\DeskPRO\HttpFoundation\Session;
use Orb\Auth\StateHandler\ArrayAccessWrapper;

class SessionStateHandler extends ArrayAccessWrapper
{
	/**
	 * @var \Application\DeskPRO\HttpFoundation\Session
	 */
	protected $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
		$this->setPrefix('authstate_');
	}
}
