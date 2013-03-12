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

namespace Application\DeskPRO\BlobStorage\StorageAdapter;

use Orb\Util\OptionsArray;
use Application\DeskPRO\BlobStorage\Blob;

abstract class AbstractStorageAdapter
{
	/**
	 * @var \Orb\Util\OptionsArray
	 */
	protected $options;

	public function __construct(array $options = null)
	{
		if ($options) {
			if (is_array($options)) {
				$options = new OptionsArray($options);
			}
			if (!($options instanceof OptionsArray)) {
				throw new \InvalidArgumentException("\$options must be an array or an instance of OptionsArray");
			}
		} else {
			$options = new OptionsArray();
		}

		$this->options = $options;

		$this->init();
	}

	protected function init() {}


	/**
	 * @param string $path
	 * @return bool
	 */
	abstract public function checkBlobExists(Blob $blob);

	/**
	 * @param \Application\DeskPRO\BlobStorage\Blob $blob
	 * @return bool
	 */
	abstract public function deleteBlob(Blob $blob);

	/**
	 * @param \Application\DeskPRO\BlobStorage\Blob $blob
	 * @param $data
	 * @return mixed
	 */
	abstract public function writeBlobString(Blob $blob, $data);

	/**
	 * @param \Application\DeskPRO\BlobStorage\Blob $blob
	 * @param resource $data
	 * @return int
	 */
	abstract public function writeBlobFromStream(Blob $blob, $fp_source);

	/**
	 * @param \Application\DeskPRO\BlobStorage\Blob $blob
	 * @param $data
	 * @return mixed
	 */
	abstract public function writeBlobFromFile(Blob $blob, $source_path);

	/**
	 * Loads the entire blob into a string
	 *
	 * @param \Application\DeskPRO\BlobStorage\Blob $blob
	 * @return string
	 */
	abstract public function readBlobString(Blob $blob);

	/**
	 * @param \Application\DeskPRO\BlobStorage\Blob $blob
	 * @param $target_path
	 * @return int
	 */
	abstract public function readBlobToFile(Blob $blob, $target_path);

	/**
	 * @param \Application\DeskPRO\BlobStorage\Blob $blob
	 * @param resource $data
	 * @return int
	 */
	abstract public function readBlobToStream(Blob $blob, $fp_target);
}