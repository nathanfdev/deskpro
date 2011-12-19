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

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity;
use Application\DeskPRO\App;

use Orb\Util\Arrays;

abstract class AbstractGatewayProcessor
{
	const EVENT_PROCESS_BLOBS       = 'DeskPRO_onEmailGatewayProcessBlobs';

	/**
	 * @var \Application\DeskPRO\EmailGateway\Reader\AbstractReader
	 */
	protected $reader;

	/**
	 * @var \Application\DeskPRO\Entity\EmailGateway
	 */
	protected $gateway;

	/**
	 * @var \Application\DeskPRO\Entity\EmailGatewayAddress
	 */
	protected $gateway_address;

	/**
	 * @var \Symfony\Bundle\FrameworkBundle\ContainerAwareEventDispatcher
	 */
	protected $event_dispatcher;

	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * @var \Application\DeskPRO\Entity\Blob[]
	 */
	protected $processed_blobs = null;

	/**
	 * @var \Orb\Input\Cleaner\Cleaner
	 */
	protected $cleaner;

	/**
	 * @var \Orb\Log\Writer\Stream
	 */
	public $logger;

	public function __construct(Entity\EmailGateway $gateway, AbstractReader $reader, array $options = array())
	{
		$this->gateway      = $gateway;
		$this->reader       = $reader;
		$this->options      = $options;

		if (isset($options['event_dispatcher'])) {
			$this->event_dispatcher = $options['event_dispatcher'];
		} else {
			$this->event_dispatcher = App::getEventDispatcher();
		}

		$this->cleaner = App::get('deskpro.core.input_cleaner');

		$address_matcher = App::getSystemService('gateway_address_matcher');
		$this->gateway_address = $address_matcher->getMatchingAddressFromReader($reader, $this->gateway);

		if (isset($options['logger'])) {
			$this->logger = $options['logger'];
		}

		$this->logMessage(sprintf("Matched address %s (%d)", $this->gateway_address->getTitle(), $this->gateway_address->id));

		$this->init();
	}

	public function logMessage($message, $pri = 'info')
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

			$desc = App::getApi('filestorage')->createRandomPath();
			$desc->write($attach->getFileContents(), array(
				'content_type' => $attach->getMimeType(),
				'filename' => $attach->getFileName()
			));

			$blob_id = $desc->getPath();
			$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

			$this->logMessage(sprintf("Processed blob %s (%i)", $blob->filename, $blob->id));
			$this->processed_blobs[] = $blob;
		}

		$ev = $this->createGatewayEvent(array('processed_blobs' => $this->processed_blobs));
		$this->event_dispatcher->dispatch(self::EVENT_PROCESS_BLOBS, $ev);
		$this->processed_blobs = $ev->processed_blobs;

		return $this->processed_blobs;
	}


	/**
	 * @return \Application\DeskPRO\Entity\EmailGateway
	 */
	public function getGateway()
	{
		return $this->gateway;
	}


	/**
	 * @return \Application\DeskPRO\Entity\EmailGatewayAddress
	 */
	public function getGatewayAddress()
	{
		return $this->gateway_address;
	}


	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\AbstractReader
	 */
	public function getReader()
	{
		return $this->reader;
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
	 * @return \Application\DeskPRO\EmailGateway\GatewayEvent
	 */
	public function createGatewayEvent(array $data = array())
	{
		return new GatewayEvent($this, $data);
	}


	/**
	 * @return \Symfony\Bundle\FrameworkBundle\ContainerAwareEventDispatcher
	 */
	public function getEventManager()
	{
		return $this->event_dispatcher;
	}
}
