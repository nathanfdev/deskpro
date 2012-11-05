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
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
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
		// Already logged in
		if ($this->session->getPerson() && $this->session->getPerson()->is_agent && $this->session->getPerson()->can_admin) {
			return $this->redirectRoute('admin');
		}

		$local_usersources = $this->em->getRepository('DeskPRO:Usersource')->getLocalInputUsersources();

		$agent_session = null;
		if (empty($local_usersources)) {
			$agent_session_code = !empty($_COOKIE['dpsid-agent']) ? $_COOKIE['dpsid-agent'] : false;
			if ($agent_session_code) {
				$agent_session = $this->em->getRepository('DeskPRO:Session')->getSessionFromCode($agent_session_code);
				if (!$agent_session || !$agent_session->person || !$agent_session->person->is_agent) {
					$agent_session = null;
				}
			}
		}

		$url = $this->generateUrl('admin', array(), true);
		if ($this->in->getString('return')) {
			$url = $this->in->getString('return');
		}

		// Makes the initial login after install cleaner without auto-redirect to license, then back to welcome
		if ($url) {
			if (preg_match('#/admin/license#', $url) && !$this->container->getSetting('core.setup_initial')) {
				$url = $this->generateUrl('admin', array(), true);
			}
		}

		$failed_login_name = false;
		if ($this->session->has('failed_login_name')) {
			$failed_login_name = $this->session->get('failed_login_name');
			$this->session->remove('failed_login_name');
			$this->session->save();
		}

		$has_logged_out = $this->in->checkIsset('o');
		return $this->render('AdminBundle:Login:index.html.twig', array(
			'return' => $url,
			'agent_session' => $agent_session,
			'failed_login_name' => $failed_login_name,
			'has_logged_out' => $has_logged_out
		));
	}
}
