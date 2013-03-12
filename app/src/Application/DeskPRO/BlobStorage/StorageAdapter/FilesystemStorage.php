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

use Application\DeskPRO\BlobStorage\Blob;

class FilesystemStorage extends AbstractStorageAdapter implements ReadStreamInterface, WriteStreamInterface
{
	/**
	 * @var string
	 */
	protected $base_path;

	protected function init()
	{
		$this->base_path = rtrim($this->options->get('base_path'), '/\\');
	}


	/**
	 * @param \Application\DeskPRO\BlobStorage\Blob $blob
	 * @return bool
	 */
	public function checkBlobExists(Blob $blob)
	{
		$path = $this->resolvePath($blob->getPath());

		return is_file($path);
	}


	/**
	 * @param \Application\DeskPRO\BlobStorage\Blob $blob
	 * @return bool
	 */
	public function deleteBlob(Blob $blob)
	{
		$path = $this->resolvePath($blob->getPath());

		if (!file_exists($path)) {
			return true;
		}

		return unlink($path);
	}


	/**
	 * @param \Application\DeskPRO\BlobStorage\Blob $blob
	 * @param $data
	 * @return int
	 */
	public function writeBlobString(Blob $blob, $data)
	{
		$fp = $this->getBlobWriteStream($blob);
		$ret = fwrite($fp, $data);
		fclose($fp);

		return $ret;
	}


	/**
	 * @param \Application\DeskPRO\BlobStorage\Blob $blob
	 * @param string $source_path
	 * @return int
	 */
	public function writeBlobFromFile(Blob $blob, $source_path)
	{
		$fp_source = fopen($source_path, 'r');

		if (!$fp_source) {
			@fclose($fp_source);
			throw new \RuntimeException("Could not open \$source_path for reading");
		}

		try {
			$ret = $this->writeBlobFromStream($blob, $fp_source);
		} catch (\Exception $e) {
			@fclose($fp_source);
			throw $e;
		}

		@fclose($fp_source);
		return $ret;
	}


	/**
	 * @param \Application\DeskPRO\BlobStorage\Blob $blob
	 * @param resource $data
	 * @return int
	 */
	public function writeBlobFromStream(Blob $blob, $fp_source)
	{
		$fp = $this->getBlobWriteStream($blob);
		$ret = stream_copy_to_stream($fp, $fp_source);
		fclose($fp);

		return $ret;
	}


	/**
	 * Loads the entire blob into a string
	 *
	 * @param \Application\DeskPRO\BlobStorage\Blob $blob
	 * @return string
	 */
	public function readBlobString(Blob $blob)
	{
		$fp = $this->getBlobReadStream($blob);

		$str = '';
		while (!feof($fp)) {
			$str .= fread($fp, 1000);
		}

		fclose($fp);

		return $str;
	}


	/**
	 * @param \Application\DeskPRO\BlobStorage\Blob $blob
	 * @param string $target_path
	 * @return int
	 */
	public function readBlobToFile(Blob $blob, $target_path)
	{
		$fp_target = fopen($target_path, 'w');

		if (!$fp_target) {
			@fclose($fp_target);
			throw new \RuntimeException("Could not open \$target_path for writing");
		}

		try {
			$ret = $this->readBlobToStream($blob, $fp_target);
		} catch (\Exception $e) {
			@fclose($fp_target);
			throw $e;
		}

		return $ret;
	}


	/**
	 * @param \Application\DeskPRO\BlobStorage\Blob $blob
	 * @param resource $fp_target
	 * @return int
	 */
	public function readBlobToStream(Blob $blob, $fp_target)
	{
		$fp = $this->getBlobReadStream($blob);
		$ret = stream_copy_to_stream($fp, $fp_target);
		fclose($fp);

		return $ret;
	}


	/**
	 * @return resource
	 */
	public function getBlobWriteStream(Blob $blob)
	{
		$path = $this->resolvePath($blob->getPath());

		$fp = fopen($path, 'r');

		if (!$fp) {
			throw new \RuntimeException("Could not open blob for writing");
		}

		return $fp;
	}


	/**
	 * @return resource
	 */
	public function getBlobReadStream(Blob $blob)
	{
		$path = $this->resolvePath($blob->getPath());

		$fp = fopen($path, 'r');

		if (!$fp) {
			throw new \RuntimeException("Could not open blob for reading");
		}

		return $fp;
	}


	/**
	 * Get the full path from a path string
	 *
	 * @param string $path
	 * @return string
	 */
	public function resolvePath($path)
	{
		$path = trim($path, '/\\');
		return $this->base_path . DIRECTORY_SEPARATOR . $path;
	}
}