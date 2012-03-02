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
 * @subpackage FileStorage
 */

namespace Application\DeskPRO\FileStorage;

use Application\DeskPRO\App;
use Zend\Service\Amazon\S3\S3;
use Orb\Util\Util;

/**
 * This handler stores metadata in the database but actual blobs in the filesystem.
 */
class AmazonS3 extends \Orb\FileStorage\AbstractStorage
{
	/**
	 * Database connection to use
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * Base filepath for the filesystem
	 * @var string
	 */
	protected $base_path;

	/**
	 * Amazon S3 bucket to use
	 * @var string
	 */
	protected $bucket;

	/**
	 * Object prefix for everything we save to the bucket
	 * @var string
	 */
	protected $prefix;

	/**
	 * @var \Zend\Service\Amazon\S3\S3
	 */
	protected $s3;

	public function __construct($access_key, $secret_key, $bucket, $base_path, \Application\DeskPRO\DBAL\Connection $db, $prefix = '')
	{
		if (!$db) {
			$db = App::getDb();
		}

		$this->db = $db;
		$this->base_path = $base_path;

		$this->bucket = $bucket;
		$this->prefix = $prefix;
		$this->s3 = new S3($access_key, $secret_key);
	}


	/**
	 * Gets a file descriptor object for a certain path. Note that this
	 * path might not exist.
	 *
	 * @return \Orb\FileStorage\FileDescriptor\Filesystem
	 */
	public function getFileDescriptor($blob_id)
	{
		$blob = $this->db->fetchAssoc("SELECT * FROM blobs WHERE id = ?", array($blob_id));
		if (!$blob) {
			$blob = null;
		}

		if (!$blob || $blob['storage_loc'] == 's3fs') {
			$desc = new FileDescriptor\Filesystem($blob, $this->base_path, $this->db);
			$desc->enableIsPreS3();
		} else {
			$desc = new FileDescriptor\AmazonS3($blob, $this->db, $this->s3, $this->bucket, $this->prefix);
		}

		return $desc;
	}


	/**
	 * Get a file descriptor object with a new, randomly generated path. This is
	 * useful for storing things like attachments, where the filename doesn't matter
	 * because the real name is stored somewhere else.
	 *
	 * @return \Orb\FileStorage\FileDescriptor\AbstractFileDescriptor
	 */
	public function createRandomPath()
	{
		$desc = new FileDescriptor\Filesystem(null, $this->base_path, $this->db);
		$desc->enableIsPreS3();

		return $desc;
	}
}
