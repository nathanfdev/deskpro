<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
*/

namespace Application\AdminInterfaceBundle\Controller;

use Application\DeskPRO\App\Assets\RequireJsConfigGenerator as AppsRequireJsConfigGenerator;
use Application\DeskPRO\Entity\ApiToken;

class IndexController extends AbstractController
{
	public function interfaceAction()
	{
		$token = new ApiToken();
		$token->scope = ApiToken::SCOPE_SESSION;
		$token->person = $this->person;
		$token->date_expires = new \DateTime("+1 hour");

		$this->em->persist($token);
		$this->em->flush();

		// Default help states
		$help_states = $this->db->fetchAllKeyValue("
			SELECT name, value_str
			FROM people_prefs
			WHERE name LIKE 'inhelp.%'
		");

		$inhelp_states = array();
		foreach ($help_states as $k => $v) {
			$k = preg_replace('#^inhelp\.#', '', $k);
			$inhelp_states[$k] = $v;
		}

		$rjs_apps = new AppsRequireJsConfigGenerator(
			$this->container->getAppManager(),
			$this->generateUrl('serve_file_root') . '/apps'
		);
		$rjs_apps_config = $rjs_apps->generateRequireJsConfigCode();

		return $this->render('AdminInterfaceBundle:Index:interface.html.twig', array(
			'api_token'             => $token,
			'session'               => $this->session->getEntity(),
			'initial_request_token' => $this->session->generateSecurityToken('request_token', 600),
			'inhelp_states'         => $inhelp_states,
			'rjs_apps_config'       => $rjs_apps_config,
			'redirect_license'      => defined('DP_BILLING_ERROR')
		));
	}
}