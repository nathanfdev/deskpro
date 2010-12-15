<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage FileStorage
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\FileStorage;

use \Orb\Util\Util;

/**
 * AmazonS3 storage handler
 *
 * @TESTME
 */
class AmazonS3 extends AbstractStorage
{
	/**
	 * The S3 object to connect to AmazonS3.
	 * @var Zend_Service_Amazon_S3
	 */
    protected $s3;

	/**
	 * The bucket name.
	 * @var string
	 */
	protected $bucket;

	public function __construct($aws_access_key, $aws_secret_key, $bucket, $use_ssl = true)
	{
		$this->s3 = new Zend_Service_Amazon_S3(
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
	 * @return Zend_Service_Amazon_S3
	 */
	public function getS3()
	{
		return $this->s3;
	}
}