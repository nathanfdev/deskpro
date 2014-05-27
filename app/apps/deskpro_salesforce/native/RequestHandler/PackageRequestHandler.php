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

namespace deskpro_salesforce\RequestHandler;

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
				break;
			default:
				throw $context->createNotFoundException();
		}
	}


	/**
	 * @param ApiPackageRequestContext $context
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function testSettingsAction(ApiPackageRequestContext $context)
	{
		$user     = $context->getIn()->getString('api_user');
		$password = $context->getIn()->getString('api_password');
		$token    = $context->getIn()->getString('api_security_token');

		$error = false;
		$client = null;

		$log = array();
		$log[] = "user: $user";
		$log[] = "password: $password";
		$log[] = "token: $token";

		$tests = array();
		$tests[] = function() use (&$log) {
			$log[] = "Verifying SoapClient is available...";
			if (!class_exists('\SoapClient')) {
				$log[] = "SOAP support is not enabled in PHP";
				return array('missing_soap', "SOAP support is not enabled in PHP");
			}
			$log[] = "SoapClient is ok";
			return null;
		};

		$tests[] = function() use (&$log) {
			$log[] = "Verifying curl is available...";
			if (!function_exists('curl_init')) {
				$log[] = "curl is not enabled in PHP";
				return array('missing_soap', "curl support is not enabled in PHP");
			}
			$log[] = "curl is ok";
			return null;
		};

		$get_client = function($url) use (&$log, $user, $password, $token) {
			require_once(DP_ROOT . '/vendor-src/salesforce/SforcePartnerClient.php');

			try {
				$sforce = new \SforcePartnerClient();
				$sforce->createConnection(DP_ROOT . '/vendor-src/salesforce/partner.wsdl.xml');
			} catch (\Exception $e) {
				$log[] = "Failed to create partner client";
				return null;
			}

			try {
				$sforce->login($user, $password . $token);
			} catch (\Exception $e) {
				$log[] = "Failed to log in: Invalid API user, password or token, or network connection failed";
				if ($e->getMessage()) {
					$log[] = "(Exception: {$e->getCode()} {$e->getMessage()}";
				}
				return null;
			}

			return $sforce;
		};

		$tests[] = function() use (&$log, &$client, $get_client) {
			$log[] = "Connecting to Salesforce service...";
			$error = error_reporting();
			error_reporting($error & ~E_WARNING);
			$client = $get_client();
			error_reporting($error);

			if ($client) {
				$log[] = "Successfully connected to SOAP service";
			} else {
				$log[] = "Failed";
				return array('failed_connection', "Invalid URL or the service refused the connection");
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