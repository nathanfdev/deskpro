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

use \Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use \Application\DeskPRO\Entity;

abstract class AbstractGateway
{
	/**
	 * @var \Application\DeskPRO\EmailGateway\Reader\AbstractReader
	 */
	protected $reader;

	/**
	 * @var \Application\DeskPRO\Entity\EmailGateway
	 */
	protected $gateway;

	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * @var \Application\DeskPRO\Entity\Blob[]
	 */
	protected $processed_blobs = null;

	public function __construct(Entity\EmailGateway $gateway, AbstractReader $reader, array $options = array())
	{
		$this->gateway = $gateway;
		$this->reader  = $reader;
		$this->options = $options;
		$this->run();
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

		return $this->processed_blobs;
	}
}