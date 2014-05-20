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

namespace deskpro_us_joomla\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestHandlerInterface;
use deskpro_us_joomla\Usersource\Auth\Joomla;
use Orb\Log\Logger;
use Orb\Log\Writer\ArrayWriter;

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
		$joomla = new Joomla(array(
			'joomla_url'    => $context->getIn()->getString('joomla_url'),
			'joomla_secret' => $context->getIn()->getString('joomla_secret'),
		));

		$ar_log = new ArrayWriter();
		$logger = new Logger();
		$logger->addWriter($ar_log);

		$joomla->setLogger($logger);

		$result_data = array(
			'log' => '',
			'error' => false,
			'error_code' => 0
		);

		try {
			$joomla->setFormData(array(
				'username' => $context->getIn()->getString('username'),
				'password' => $context->getIn()->getString('password'),
			));

			$result = $joomla->authenticate();

			if (!$result->isValid()) {
				$result_data['error']      = $result->getMessages('error_message') ?: 'Invalid login';
				$result_data['error_code'] = $result->getMessages('error_code') ?: 'general';
			}
		} catch (\Exception $e) {
			$result_data['error'] = $e->getMessage();
			$result_data['error_code'] = $e->getCode();
		}

		$result_data['log'] = $ar_log->getMessagesAsString();

		return $context->createJsonResponse($result_data);
	}
}