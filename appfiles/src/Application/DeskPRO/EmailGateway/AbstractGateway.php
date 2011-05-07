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

abstract class AbstractGateway
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

	public function __construct(Entity\EmailGateway $gateway_info, AbstractReader $reader, array $options = array())
	{
		$this->gateway_info = $gateway_info;
		$this->reader       = $reader;
		$this->options      = $options;

		if (isset($options['event_dispatcher'])) {
			$this->event_dispatcher = $options['event_dispatcher'];
		} else {
			$this->event_dispatcher = App::getEventDispatcher();
		}

		$this->init();
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
				'content_type' => $file->getMimeType(),
				'filename' => $file->getFileName()
			));

			$blob_id = $desc->getPath();
			$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

			$this->processed_blobs[] = $blobs;
		}

		$ev = $this->createGatewayEvent(array('processed_blobs' => $this->processed_blobs));
		$this->event_dispatcher->dispatch(self::EVENT_PROCESS_BLOBS, $ev);
		$this->processed_blobs = $ev->processed_blobs;

		return $this->processed_blobs;
	}


	/**
	 * @return \Application\DeskPRO\Entity\EmailGateway
	 */
	public function getGatewayInfo()
	{
		return $this->gateway_info;
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