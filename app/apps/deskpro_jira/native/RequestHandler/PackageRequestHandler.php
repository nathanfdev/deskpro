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
 * @category Entities
 */

namespace deskpro_jira\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestHandlerInterface;

class PackageRequestHandler implements ApiPackageRequestHandlerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function handleApiPackageRequest(ApiPackageRequestContext $context)
	{
		switch ($context->getAction()) {
			case 'test-settings':
				return $this->testSettingsAction($context);
			case 'check-requirements':
				return $this->checkRequirementsAction($context);
			default:
				throw $context->createNotFoundException();
		}
	}


	/**
	 * @param ApiPackageRequestContext $context
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function checkRequirementsAction(ApiPackageRequestContext $context)
	{
		return $context->createJsonResponse(array('curl_support' => function_exists('curl_init')));
	}

	/**
	 * @param ApiPackageRequestContext $context
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function testSettingsAction(ApiPackageRequestContext $context)
	{
		$user = $context->getIn()->getString('jira_username');
		$password = $context->getIn()->getString('jira_password');
		$url = $context->getIn()->getString('jira_url');
		$em = $context->getEm();
		$regEnabled = $context->getContainer()->getSetting('core.reg_enabled');

		$error = false;
		$client = null;

		$log = array();
		$log[] = 'username: ' . $user;
		$log[] = 'password: ' . $password;
		$log[] = 'url: ' . $url;


		$tests = array();
		$tests[] = function() use (&$log, $url, $user, $password, $em, $regEnabled) {

			$log[] = "Verifying JIRA API...";

			$service = new \Orb\Jira\Service($url, array(
				'username'	=> $user,
				'password'	=> $password,
				'debug'		=> DP_DEBUG,
				'reg_enabled' => $regEnabled,
			), $em);

			try {
				$meta = $service->getCreateMeta();
				$log[] = 'Everything is ok';
			} catch (\Exception $e) {
				$log[] = $e->getMessage();
				return array($e->getCode(), 'API Exception');
			}
		};

		foreach ($tests as $t) {
			$error = $t();
			if ($error) {
				break;
			}
		}

		$result_data = array(
			'log'        => implode("\n", $log),
			'error'      => $error ? $error[1] : false,
			'error_code' => $error ? $error[0] : false
		);

		return $context->createJsonResponse($result_data);
	}
}