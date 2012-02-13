<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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
	 * @var
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

			if ($verbose) {
				$to = array();
				foreach ($reader->getToAddresses() as $x) {
					$to[] = $x->getEmail();
				}
				$to = implode(', ', $to);

				$subj = substr($reader->getSubject()->getSubject(), 0, 40);
				$this->logger->log("[Message] To: $to :: $subj", 'debug');
			}

			App::getOrm()->beginTransaction();

			try {
				/** @var $proc \Application\DeskPRO\EmailGateway\AbstractGatewayProcessor */
				$proc = $gateway->getNewProcessor($reader, array('logger' => $this->logger));
				$created_obj = $proc->run();

				$source['status'] = 'complete';
				App::getOrm()->persist($source);
				App::getOrm()->flush();

				App::getOrm()->commit();

				if ($verbose) {
					$this->logger->log("Created " . get_class($created_obj) . ": " . $created_obj->getId(), 'info');
				}
			} catch (\Exception $e) {
				App::getOrm()->rollback();

				throw $e;
			}
		}

		$end_time = microtime(true);
		$this->logger->log(sprintf("Finished processing gateway. Took %.2f seconds.", $end_time - $start_time), 'info');
	}
}
