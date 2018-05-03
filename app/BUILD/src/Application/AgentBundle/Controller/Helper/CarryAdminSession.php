<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller\Helper;

use Application\DeskPRO\App;

class CarryAdminSession
{
    /** @var string */
    protected $controller;
    /** @var string */
    protected $cookie_name;

    public function __construct($controller, $cookie_name = 'dpsid-agent')
    {
        $this->controller  = $controller;
        $this->cookie_name = $cookie_name;
    }

    public function process()
    {
        if (!$this->controller->person->id) {
            $admin_session_code = !empty($_COOKIE[$this->cookie_name]) ? $_COOKIE[$this->cookie_name] : false;
            $admin_session      = null;
            if ($admin_session_code) {
                $admin_session = App::getEntityRepository('DeskPRO:Session')->getSessionFromCode($admin_session_code);
                if (!$admin_session || !$admin_session->person || !$admin_session->person->is_agent) {
                    $admin_session = null;
                }

                if ($admin_session) {
                    $this->controller->session->set('auth_person_id', $admin_session->person->id);
                    \Application\DeskPRO\HttpFoundation\Cookie::makeDeleteCookie('dp-guest-cache')->send();
                    $this->controller->session->set('dp_interface', DP_INTERFACE);

                    // Set their status to available by default
                    $this->controller->session->set('active_status', 'available');

                    if ($admin_session->person->hasPerm('agent_chat.use')) {
                        $this->controller->session->set('is_chat_available', 0);
                    } else {
                        $this->controller->session->set('is_chat_available', 1);
                    }

                    $this->controller->session->save();

                    $this->controller->person = $admin_session->person;
                    App::setCurrentPerson($admin_session->person);
                }
            }
        }
    }
}
