<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\Controller\Helper\LoginHelper;

use Application\DeskPRO\App;

class LoginController extends \Application\UserBundle\Controller\LoginController
{
	protected $tpl_prefix = 'AdminBundle:Login';
	protected $route_prefix = 'admin';

	/**
	 * Handles showing the login form, and on POST handles login credentials
	 * through the auth adapters.
	 */
	public function indexAction()
	{
		$agent_session_code = !empty($_COOKIE['dpsid-agent']) ? $_COOKIE['dpsid-agent'] : false;
		$agent_session = null;
		if ($agent_session_code) {
			$agent_session = App::getEntityRepository('DeskPRO:Session')->getSessionFromCode($agent_session_code);
			if (!$agent_session || !$agent_session->person || !$agent_session->person->is_agent) {
				$agent_session = null;
			}
		}

		$url = $this->generateUrl('admin', array(), true);
		if ($this->in->getString('return')) {
			$url = $this->in->getString('return');
		}

		return $this->render('AdminBundle:Login:index.html.twig', array('return' => $url, 'agent_session' => $agent_session));
	}
}
