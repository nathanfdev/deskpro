<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller\Helper;

use Application\DeskPRO\App;

class CarryAdminSession
{
	protected $controller;

	public function __construct($controller)
	{
		$this->controller = $controller;
	}

	public function process()
	{
		if (!$this->controller->person->id) {
			$admin_session_code = !empty($_COOKIE['dpsid-admin']) ? $_COOKIE['dpsid-admin'] : false;
			$admin_session = null;
			if ($admin_session_code) {
				$admin_session = App::getEntityRepository('DeskPRO:Session')->getSessionFromCode($admin_session_code);
				if (!$admin_session || !$admin_session->person || !$admin_session->person->is_agent) {
					$admin_session = null;
				}

				if ($admin_session) {
					$this->controller->session->set('auth_person_id', $admin_session->person->id);
					$this->controller->session->set('dp_interface', DP_INTERFACE);
					$this->controller->session->save();

					$this->controller->person = $admin_session->person;
					App::setCurrentPerson($admin_session->person);
				}
			}
		}
	}
}
