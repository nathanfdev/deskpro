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

namespace Application\ReportBundle\Controller;

use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\Controller\Helper\LoginHelper;

use Application\DeskPRO\App;

class LoginController extends \Application\UserBundle\Controller\LoginController
{
	protected $tpl_prefix = 'ReportBundle:Login';
	protected $route_prefix = 'report';

	/**
	 * Handles showing the login form, and on POST handles login credentials
	 * through the auth adapters.
	 */
	public function indexAction()
	{
		$agent_session_code = $this->in->getString('dpsid-agent');
		$agent_session = null;
		if ($agent_session_code) {
			$agent_session = App::getEntityRepository('DeskPRO:Session')->getSessionFromCode($agent_session_code);
			if (!$agent_session || !$agent_session->person || !$agent_session->person->is_agent) {
				$agent_session = null;
			}
		}

		$url = $this->generateUrl('report', array(), true);
		return $this->render('ReportBundle:Login:index.html.twig', array('return' => $url, 'agent_session' => $agent_session));
	}
}
