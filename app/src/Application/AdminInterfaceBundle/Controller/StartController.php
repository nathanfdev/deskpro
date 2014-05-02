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
 */

namespace Application\AdminInterfaceBundle\Controller;

use Application\DeskPRO\Entity\ApiToken;

class StartController extends AbstractController
{
	####################################################################################################################
	# index
	####################################################################################################################

	public function indexAction()
	{
		$token = new ApiToken();
		$token->scope = ApiToken::SCOPE_SESSION;
		$token->person = $this->person;
		$token->date_expires = new \DateTime("+1 hour");

		$this->em->persist($token);
		$this->em->flush();

		$php_path = $this->container->getPhpBinaryPath();
		$php_path_set = dp_get_config('php_path');

		return $this->render('AdminInterfaceBundle:Start:layout.html.twig', array(
			'api_token'              => $token,
			'session'                => $this->session->getEntity(),
			'initial_request_token'  => $this->session->generateSecurityToken('request_token', 600),
			'php_path'               => $php_path,
			'php_path_set'           => $php_path_set,
		));
	}
}