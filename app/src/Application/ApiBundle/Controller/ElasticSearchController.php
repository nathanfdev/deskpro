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

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\DeskPRO\Monolog\Logger;
use DeskPRO\Kernel\KernelErrorHandler;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use Monolog\Handler\NullHandler;

class ElasticSearchController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new AdminManagePermission();
	}

	####################################################################################################################
	# get-settings
	####################################################################################################################

	public function getSettingsAction()
	{
		$values = array(
			'enabled'        => (bool)$this->settings->get('elastica.enabled'),
			'requires_reset' => (bool)$this->settings->get('elastica.requires_reset'),
			'host'           => $this->settings->get('elastica.clients.default.host') ? : '',
			'port'           => (int)$this->settings->get('elastica.clients.default.port') ? : 9200
		);

		return $this->createApiResponse(array('elastic_settings' => $values));
	}

	####################################################################################################################
	# save-settings
	####################################################################################################################

	public function saveSettingsAction()
	{
		$was_enabled = $this->settings->get('elastic_settings.enabled');

		$this->settings->setSetting('elastica.enabled', $this->in->getBoolInt('elastic_settings.enabled'));
		$this->settings->setSetting('elastica.clients.default.host', $this->in->getString('elastic_settings.host') ? : '');
		$this->settings->setSetting('elastica.clients.default.port', $this->in->getUint('elastic_settings.port') ? : 9200);

		// Just turned on, we need to toggle the requires_reset flag
		if (!$was_enabled && $this->in->getBool('elastic_settings.enabled')) {
			$this->settings->setSetting('elastica.requires_reset', 1);
		} else {
			$this->settings->setSetting('elastica.requires_reset', null);
		}

		return $this->createApiSuccessResponse();
	}

	####################################################################################################################
	# test-settings
	####################################################################################################################

	public function testSettingsAction()
	{
		$config = array(
			'host' => $this->in->getString('host'),
			'port' => $this->in->getString('port')
		);

		#------------------------------
		# Configure logger
		#------------------------------

		$logger = new Logger('elastic_test');
		$logger->enableSavedMessages();

		$elastica_logger = new ElasticaLogger($logger);
		$elastica_logger->debug(sprintf("Host: %s", $config['host']));
		$elastica_logger->debug(sprintf("Port: %s", $config['port']));

		$config['logger'] = $elastica_logger;

		#------------------------------
		# Verify params
		#------------------------------

		$error = false;
		if (!$config['host']) {
			$elastica_logger->error("Missing host");
			$error = true;
		}
		if (!$config['port']) {
			$elastica_logger->error("Missing port");
			$error = true;
		}

		if ($error) {
			return $this->createApiResponse(array('is_success' => false, 'log' => $logger->getSavedMessages()));
		}

		#------------------------------
		# Create client
		#------------------------------

		/** @var \Application\DeskPRO\Elastica\ClientFactory $client_factory */
		$client_factory = $this->container->get('deskpro.elastica.client_factory');

		$client = $client_factory->createClientByConfig($config);

		#------------------------------
		# Test client
		#------------------------------

		$ts_start = microtime(true);

		try {
			$elastica_logger->debug("Fetching status...");
			$status = $client->getStatus();
			$elastica_logger->debug(sprintf("Done in %.3fs", microtime(true) - $ts_start));

			if ($status->getResponse()->isOk()) {
				$elastica_logger->info("Success. Response: " . json_encode($status->getResponse()->getData()));
			} else {
				$elastica_logger->error("Failed: " . $status->getResponse()->getError());
			}
		} catch (\Exception $e) {
			$elastica_logger->error("Exception: {$e->getMessage()}");
			dp_log(KernelErrorHandler::formatBacktrace($e->getTrace()));
			$error = true;
		}

		return $this->createApiResponse(array(
			'is_success' => !$error,
			'log' => $logger->getSavedMessages()
		));
	}
}