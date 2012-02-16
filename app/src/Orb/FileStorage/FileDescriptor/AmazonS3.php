<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage FileStorage
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\FileStorage\FileDescriptor;

use Zend\Service\Amazon\S3\S3;
use Orb\FileStorage;

/**
 * Working with the local filesystem
 *
 * @TESTME
 */
class AmazonS3 extends AbstractFileDescriptor
{
	/**
	 * The S3 object to connect to AmazonS3.
	 * @var \Zend\Service\Amazon\S3
	 */
	protected $s3;

	/**
	 * The bucket name.
	 * @var string
	 */
	protected $bucket;

	/**
	 * The path description provided.
	 * @var string
	 */
	protected $path;

	/**
	 * The real path with the bucket prepended. The amazons3 class used uses the convention by
	 * which the bucket is always prepended to the actual path.
	 * @var string
	 */
	protected $real_path_bucket = '';

	/**
	 * Object information about the path
	 * @var array|false
	 */
	protected $objinfo = null;

	public function __construct($s3, $bucket, $path)
	{
		$this->s3 = $s3;
		$this->bucket = $bucket;
		$this->path = $path;
		$this->real_path_bucket = $bucket . '/' . $path;
	}


	/**
	 * Get information about the paths object.
	 *
	 * @param bool $reset Re-fetch the information instead of using cached version?
	 * @return array|false
	 */
	public function getObjectInfo($reset = false)
	{
		if ($this->objinfo !== null AND !$reset) {
			return $this->objinfo;
		}

		$this->objinfo = $this->s3->getInfo($this->real_path_bucket);

		return $this->objinfo;
	}



	/**
	 * Does the file exist?
	 *
	 * @return bool
	 */
	public function exists()
	{
		return (bool)$this->getObjectInfo();
	}



	/**
	 * Delete the file at the path.
	 *
	 * @throws FileStorage\Exception When couldnt delete the file
	 */
	public function delete()
	{
		if (!$this->exists()) {
			return;
		}

		$success = $this->s3->removeObject($this->real_path_bucket);

		if (!$success) {
			throw new FileStorage\Exception('Could not delete file: ' . $this->real_path, 1);
		}

		$this->objinfo = null;
	}



	/**
	 * Write data to the file at the path.
	 *
	 * @param string $data The data to write
	 */
	public function write($data, $meta = null)
	{
		if (!is_array($meta)) $meta = $meta === null ? array() : array($meta);
		$meta[S3::S3_ACL_HEADER] = S3::S3_ACL_PUBLIC_READ;

		if ($meta[self::METADATA_FILENAME]) {
			$tmp = $meta[self::METADATA_FILENAME];
			unset($meta[self::METADATA_FILENAME]);
			$meta['File-Name'] = $tmp;
		}

		if ($meta[self::METADATA_CONTENT_TYPE]) {
			$tmp = $meta[self::METADATA_CONTENT_TYPE];
			unset($meta[self::METADATA_CONTENT_TYPE]);
			$meta['Content-Type'] = $tmp;
		}

		$this->s3->putObject($this->real_path_bucket, $data, $meta);
		$this->objinfo = null;
	}



	/**
	 * Write data from a file pointer.
	 *
	 * @param resource $fp_data The file pointer
	 * @param array $meta Any metadata (Content-Type is sometimes important)
	 */
	public function writeFromFile($fp_data, $meta = null)
	{
		if (!is_resource($fp_data)) {
			throw new FileStorage\Exception('$fp_data must be a file pointer', 5);
		}

		$this->write(stream_get_contents($fp_data), $meta);
	}



	/**
	 * Read data from the file at the path.
	 */
	public function get()
	{
		return $this->s3->getObject($this->real_path_bucket);
	}



	/**
	 * Get the filesize of the file.
	 *
	 * @return int
	 */
	public function getLength()
	{
		if (!$this->exists()) {
			return null;
		}

		$objinfo = $this->getObjectInfo();
		return $objinfo['size'];
	}



	public function getMetaData()
	{
		$objinfo = $this->getObjectInfo();
		return $objinfo;
	}



	/**
	 * Close the resource once you're done with it.
	 */
	public function release()
	{

	}



	/**
	 * Get the URL to the file.
	 *
	 * @return string
	 */
	public function getDirectLink()
	{
		return 'http://' . $this->bucket . '.' . $this->s3->getEndpoint()->getHost() . '/' . $this->path;
	}



	/**
	 * Get the path that can be used to re-create this descriptor.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}



	/**
	 * Get the bucket
	 *
	 * @return string
	 */
	public function getBucket()
	{
		return $this->bucket;
	}
}
