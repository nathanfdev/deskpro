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

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;

/**
 * This runs collection and processsing in gateways
 */
class Runner
{
	/**
	 * @var \Application\DeskPRO\Log\Logger
	 */
	protected $logger;

	/**
	 * @var \Application\DeskPRO\Entity\EmailGateway[]
	 */
	protected $gateways;

	public function __construct()
	{
		$this->logger = new \Application\DeskPRO\Log\Logger();
	}


	/**
	 * @param $logger \Application\DeskPRO\Log\Logger
	 */
	public function setLogger(\Application\DeskPRO\Log\Logger $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * Set the gateways to process
	 *
	 * @param $gateways
	 */
	public function setGateways(array $gateways)
	{
		$this->gateways = $gateways;
	}


	/**
	 * Load gateways from the database
	 *
	 * @param bool $include_disabled True to also include disabled gateways
	 */
	public function loadGatewaysFromDb($include_disabled = false)
	{
		if ($include_disabled) {
			$this->gateways = App::getOrm()->getRepository('DeskPRO:EmailGateway')->findAll();
		} else {
			$this->gateways = App::getOrm()->getRepository('DeskPRO:EmailGateway')->getAllEnabled();
		}
	}


	/**
	 * @throws \Exception
	 */
	public function execute()
	{
		foreach ($this->gateways as $gateway) {
			$this->executeGateway($gateway);
		}
	}

	/**
	 * Execute a gateway
	 *
	 * @param \Application\DeskPRO\EntityRepository\EmailGateway $gateway
	 * @throws \Exception
	 */
	public function executeGateway(\Application\DeskPRO\Entity\EmailGateway $gateway)
	{
		$this->logger->log("Start processing {$gateway['title']} {$gateway['gateway_type']}:{$gateway['connection_type']}", 'info');
		$start_time = microtime(true);

		/** @var $fetcher \Application\DeskPRO\EmailGateway\Fetcher\AbstractFetcher */
		$fetcher = $gateway->getFetcher();
		$fetcher->setLogger($this->logger);

		while ($source = $fetcher->readNext()) {

			$this->logger->log("[Gateway {$gateway['id']}] Read source ID {$source['id']}", 'debug');

			$reader = new \Application\DeskPRO\EmailGateway\Reader\EzcReader();
			$reader->setRawSource($source['raw_source']);
			$reader->setProperty('email_source', $source);

			$to = array();
			foreach ($reader->getToAddresses() as $x) {
				$to[] = $x->getEmail();
			}
			$to = implode(', ', $to);

			$subj = substr($reader->getSubject()->getSubject(), 0, 40);
			$this->logger->log("[Message] To: $to :: $subj", 'debug');

			App::getOrm()->beginTransaction();

			try {

				$pre_processor = new PreProcessor($gateway, $reader, array('logger' => $this->logger));
				$pre_processor->run();

				$created_obj = null;
				if ($pre_processor->isValid()) {
					/** @var $proc \Application\DeskPRO\EmailGateway\AbstractGatewayProcessor */
					$proc = $gateway->getNewProcessor($reader, array('logger' => $this->logger));
					$created_obj = $proc->run();

					if ($proc->isValid()) {
						$source['status'] = 'complete';
					} else {
						$source['status'] = 'error';
						$source['error_code'] = $proc->getErrorCode();
					}

					$source['source_info'] = $proc->getSourceInfo();
				} else {
					$source['status'] = 'error';
					$source['error_code'] = $pre_processor->getErrorCode();
					$source['source_info'] = $pre_processor->getSourceInfo();
				}

				if ($created_obj) {
					$source['object_type'] = strtolower(\Orb\Util\Util::getBaseClassname($created_obj));
					$source['object_id'] = $created_obj->id;
				}
				App::getOrm()->persist($source);
				App::getOrm()->flush();

				App::getOrm()->commit();

				$this->logger->log("Created " . get_class($created_obj) . ": " . $created_obj->getId(), 'debug');
			} catch (\Exception $e) {
				App::getOrm()->rollback();

				throw $e;
			}
		}

		$end_time = microtime(true);
		$this->logger->log(sprintf("Finished processing gateway. Took %.2f seconds.", $end_time - $start_time), 'info');
	}
}
