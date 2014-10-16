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

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity;
use Orb\Util\Arrays;

abstract class AbstractGatewayProcessor
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	protected $container;

	/**
	 * @var \Application\DeskPRO\EmailGateway\Reader\AbstractReader
	 */
	protected $reader;

	/**
	 * @var \Application\DeskPRO\Entity\EmailAccount
	 */
	protected $account;

	/**
	 * @var string
	 */
	protected $account_email_address;

	/**
	 * @var string
	 */
	protected $sent_to;

	/**
	 * @var \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher
	 */
	protected $event_dispatcher;

	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * Indexed by blob id
	 * @var \Application\DeskPRO\Entity\Blob[]
	 */
	protected $processed_blobs = null;

	/**
	 * Same as processed_blobs except indexed by Content-ID
	 *
	 * @var \Application\DeskPRO\Entity\Blob[]
	 */
	protected $processed_blobs_cid = array();

	/**
	 * @var \Orb\Input\Cleaner\Cleaner
	 */
	protected $cleaner;

	/**
	 * @var \Orb\Log\Logger
	 */
	public $logger;

	public function __construct(Entity\EmailAccount $account, AbstractReader $reader, array $options = array())
	{
		$this->container    = App::getContainer();
		$this->account      = $account;
		$this->reader       = $reader;
		$this->options      = $options;

		if (isset($options['event_dispatcher'])) {
			$this->event_dispatcher = $options['event_dispatcher'];
		} else {
			$this->event_dispatcher = App::getEventDispatcher();
		}

		$this->cleaner = App::get('deskpro.core.input_cleaner');

		$this->account_email_address = '';
		foreach ($reader->getReceivedAddresses() as $addr) {
			if ($email_match = $this->account->getEmailAddressMatch($addr->getEmail())) {
				$this->account_email_address = $email_match;
			}
		}

		if (isset($options['logger'])) {
			$this->logger = $options['logger'];
		}

		if($this->account_email_address) {
			$this->logMessage(sprintf("Matched address %s", $this->account_email_address));
		} else {
			$this->logMessage(sprintf('Warning: Could not get matched address for gateway %d', $account->id));
		}

		$to_addresses = array_map(function($e) { return $e->email; }, $reader->getReceivedAddresses());
		$this->sent_to = implode(',', $to_addresses);

		$this->logMessage('sent_to: ' . $this->sent_to);

		$this->init();
	}

	public function logMessage($message, $pri = 'debug')
	{
		if ($this->logger) {
			$this->logger->log($message, $pri);
		}
	}

	/**
	 * Empty hook method for init
	 */
	protected function init()
	{

	}

	abstract public function run();

	/**
	 * Process all attachments on the email into temp blobs.
	 *
	 * @return \Application\DeskPRO\Entity\Blob[]
	 */
	protected function processBlobs()
	{
		if ($this->processed_blobs !== null) return $this->processed_blobs;
		$this->processed_blobs = array();

		foreach ($this->reader->getAttachments() as $attach) {

			$blob = App::getContainer()->getBlobStorage()->createBlobRecordFromString(
				$attach->getFileContents(),
				$attach->getFileName(),
				$attach->getMimeType()
			);

			$this->logMessage(sprintf("Processed blob %s (%d)", $blob->filename, $blob->id));
			$this->processed_blobs[$blob->id] = $blob;

			if ($attach->getContentId()) {
				$this->processed_blobs_cid[$attach->getContentId()] = $blob;
			}
		}

		return $this->processed_blobs;
	}


	/**
	 * @return \Application\DeskPRO\Entity\EmailAccount
	 */
	public function getAccount()
	{
		return $this->account;
	}


	/**
	 * @return string
	 */
	public function getAccountEmailAddress()
	{
		return $this->account_email_address;
	}


	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\AbstractReader
	 */
	public function getReader()
	{
		return $this->reader;
	}


	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEm()
	{
		return $this->container->getEm();
	}


	/**
	 * @return \Application\DeskPRO\DBAL\Connection
	 */
	public function getDb()
	{
		return $this->container->getDb();
	}


	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}


	/**
	 * Get an option. This accepts dot notation for deep array keys.
	 *
	 * @param  string  $name     The option to fetch
	 * @param  mixed   $default  The default value if the option is not set
	 * @return mixed
	 */
	public function getOption($name, $default = null)
	{
		return Arrays::getValue($this->options, $name, $default);
	}


	/**
	 * @return \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher
	 */
	public function getEventManager()
	{
		return $this->event_dispatcher;
	}


	/**
	 * @return bool
	 */
	public function isValid()
	{
		return $this->getErrorCode() === null;
	}


	/**
	 * @return bool
	 */
	public function getErrorCode()
	{
		return null;
	}


	/**
	 * @return array
	 */
	public function getSourceInfo()
	{
		return array();
	}
}
