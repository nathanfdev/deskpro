<?php

/**
 * DeskPRO.
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
