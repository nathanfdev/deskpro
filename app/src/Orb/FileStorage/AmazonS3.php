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
 * Orb
 *
 * @package Orb
 * @subpackage FileStorage
 */

namespace Orb\FileStorage;

use Zend\Service\Amazon\S3\S3;
use Orb\Util\Util;

/**
 * AmazonS3 storage handler
 *
 * @TESTME
 */
class AmazonS3 extends AbstractStorage
{
	/**
	 * The S3 object to connect to AmazonS3.
	 * @var \Zend\Service\Amazon\S3\S3
	 */
    protected $s3;

	/**
	 * The bucket name.
	 * @var string
	 */
	protected $bucket;

	public function __construct($aws_access_key, $aws_secret_key, $bucket, $use_ssl = true)
	{
		$this->s3 = new S3(
    		$aws_access_key,
    		$aws_secret_key,
    		$use_ssl
    	);

		$this->bucket = $bucket;
	}



	/**
	 * Gets a file descriptor object for a certain path. Note that this
	 * path might not exist.
	 *
	 * @return Orb\FileStorage\FileDescriptor\AmazonS3
	 */
	public function getFileDescriptor($path)
	{
		$desc = new FileDescriptor\AmazonS3($this->s3, $this->bucket, $path);
		return $desc;
	}



	/**
	 * Get a file descriptor object with a new, randomly generated path. This is
	 * useful for storing things like attachments, where the filename doesn't matter
	 * because the real name is stored somewhere else.
	 *
	 * @return Orb\FileStorage\FileDescriptor\AbstractFileDescriptor
	 */
	public function createRandomPath()
	{
		do {
			$path = date('Ymd') . '-' . Util::uuid4();

			if ($this->use_subdirs) {
				// Split y-m/d/char/uuid
				$path = preg_replace('#^(.{4})(.{2})(.{2})\-(.{1})#', '$1-$2/$3/$4/$4', $path);
			}

			$desc = $this->getFileDescriptor($path);
		} while($desc->exists());

		return $desc;
	}



	/**
	 * Get the bucket name
	 *
	 * @return string
	 */
	public function getBucket()
	{
		return $this->bucket;
	}



	/**
	 * Get the S3 object.
	 *
	 * @return \Zend\Service\Amazon\S3\S3
	 */
	public function getS3()
	{
		return $this->s3;
	}
}
