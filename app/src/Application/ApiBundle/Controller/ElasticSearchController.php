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
use Application\DeskPRO\Elastica\ClientFactory;
use Application\DeskPRO\Monolog\Logger;
use DeskPRO\Kernel\KernelErrorHandler;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use Monolog\Handler\NullHandler;
use Orb\Util\Numbers;

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
			'url'            => $this->settings->get('elastica.clients.default.url'),
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
		$this->settings->setSetting('elastica.clients.default.url', $this->in->getString('elastic_settings.url') ? : '');

		// Just turned on, we need to toggle the requires_reset flag
		if ((!$was_enabled || $this->in->getBool('reindex')) && $this->in->getBool('elastic_settings.enabled')) {
			$this->settings->setSetting('elastica.requires_reset', 1);
			$es_status = $this->em->getRepository('DeskPRO:DataStore')->getByName('sys.es_indexer', false);
			if ($es_status) {
				$this->em->remove($es_status);
				$this->em->flush();
			}
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
		#------------------------------
		# Configure logger
		#------------------------------

		try {
			$config = ClientFactory::createConfigFromUrl($this->in->getString('url'));
		} catch (\Exception $e) {
			return $this->createApiResponse(array('is_success' => false, 'log' => $e->getMessage()));
		}

		$logger = new Logger('elastic_test');
		$logger->enableSavedMessages();

		$elastica_logger = new ElasticaLogger($logger);
		$elastica_logger->debug(sprintf("URL: %s", $this->in->getString('url')));
		$elastica_logger->debug(sprintf("Host: %s", $config['host']));
		$elastica_logger->debug(sprintf("Port: %s", $config['port']));
		$elastica_logger->debug(sprintf("Path: %s", $config['path']));
		$elastica_logger->debug(sprintf("Transport: %s", $config['transport']));

		$config['logger'] = $elastica_logger;

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
		$error = false;

		try {
			$elastica_logger->debug("Fetching status...");
			$status = $client->getStatus();
			$elastica_logger->debug(sprintf("Done in %.3fs", microtime(true) - $ts_start));

			if ($status->getResponse()->isOk()) {
				$elastica_logger->info("Success. Response: " . json_encode($status->getResponse()->getData()));
			} else {
				$elastica_logger->error("Failed: " . $status->getResponse()->getError());
				$error = true;
			}
		} catch (\Exception $e) {
			$elastica_logger->error("Exception: {$e->getMessage()}");
			$error = true;
		}

		return $this->createApiResponse(array(
			'is_success' => !$error,
			'log' => $logger->getSavedMessages()
		));
	}

	####################################################################################################################
	# index-status
	####################################################################################################################

	public function indexStatusAction()
	{
		$es_status = $this->em->getRepository('DeskPRO:DataStore')->getByName('sys.es_indexer', false);

		$log_path = dp_get_log_dir() . '/es-indexer.log';
		$log = null;
		if (file_exists($log_path)) {
			$log = @file_get_contents($log_path);
		}

		$is_indexing = ($this->getContainer()->getSetting('elastica.requires_reset') || ($es_status && $es_status->getData('status') == 'running'));

		$info = null;
		if (!$is_indexing) {
			try {
				/** @var \FOS\ElasticaBundle\Client $client */
				$client = $this->container->get('fos_elastica.client.default');
				$status = $client->getStatus()->getData();

				if (!isset($status['indices']['deskpro'])) {
					$info = array('error' => 'no_index');
				} else {
					$info = array(
						'size'          => @$status['indices']['deskpro']['index']['size_in_bytes'],
						'size_readable' => Numbers::filesizeDisplay(@$status['indices']['deskpro']['index']['size_in_bytes']),
						'num_docs'      => @$status['indices']['deskpro']['docs']['num_docs'],
					);
				}

			} catch (\Exception $e) {
				$info = array('error' => 'no_status');
			}
		}

		return $this->createJsonResponse(array(
			'is_indexing'    => $is_indexing,
			'indexer_status' => $es_status ? $es_status->data : null,
			'indexer_log'    => $log ?: null,
			'info'           => $info
		));
	}
}