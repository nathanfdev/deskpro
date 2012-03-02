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

namespace Orb\FileStorage\FileDescriptor;

use \Orb\FileStorage;
use \Orb\Util\Strings;

/**
 * Working with the local filesystem
 */
class Filesystem extends AbstractFileDescriptor
{
	/**
	 * A file pointer for reading.
	 * @var resource
	 */
	protected $read_p = null;

	/**
	 * A file pointer for writing.
	 * @var resource
	 */
	protected $write_p = null;

	/**
	 * The real file path (taking into account base_path).
	 * @var string
	 */
	protected $real_path = '';

	/**
	 * The path description provided.
	 * @var string
	 */
	protected $path;

	/**
	 * The base path provided.
	 * @var string
	 */
	protected $base_path;

	/**
	 * The mode to create new directories with.
	 * @var int
	 */
	protected $dir_mode = 0755;

	/**
	 * The mode new files are created with.
	 * @var int
	 */
	protected $file_mode = 0744;


	/**
	 * @param string $path          The path description to the file
	 * @param string $base_path     The base path on the filesystem where the files are located
	 * @param int    $dir_mode      The mode newly created directories are created with
	 * @param int    $file_mode     The mode newly created files are created with
	 */
	public function __construct($path, $base_path, $dir_mode = 0755, $file_mode = 0744)
	{
		$this->base_path = rtrim(str_replace('\\', '/', $base_path), '/');
		$this->path      = ltrim(str_replace('\\', '/', $path), '/');

		$this->real_path = $this->base_path . '/' . $this->path;

		$this->dir_mode  = $dir_mode;
		$this->file_mode = $file_mode;
	}



	/**
	 * Does the file exist?
	 *
	 * @return bool
	 */
	public function exists()
	{
		return is_file($this->real_path);
	}



	/**
	 * Delete the file at the path.
	 *
	 * @throws FileStorage\Exception When couldnt delete the file
	 */
	public function delete()
	{
		// Can't delete whats not there.
		// No error here. I assume the end goal of a file not existing
		// is achieved if it's already not there!
		if (!$this->exists()) {
			return;
		}

		$success = @unlink($this->real_path);

		if (!$success) {
			throw new FileStorage\Exception('Could not delete file: ' . $this->real_path, 1);
		}
	}



	/**
	 * Write data to the file at the path.
	 *
	 * @param string $data The data to write
	 */
	public function write($data, $meta = null)
	{
		$fp = $this->getWriteFilePointer();
		@fwrite($fp, $data);
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

		$fp_write = $this->getWriteFilePointer();

		while (!feof($fp_data)) {
			$data = fread($fp_data, 8192);
			fwrite($fp_write, $data);
		}
	}


	/**
	 * Get the file pointer for writing to the file.
	 *
	 * @return resource
	 */
	protected function getWriteFilePointer()
	{
		if ($this->write_p !== null) {
			return $this->write_p;
		}

		if (!is_dir(dirname($this->real_path))) {
			if (!mkdir(dirname($this->real_path), $this->dir_mode, true)) {
				throw new FileStorage\Exception('Could not create directorie(s) for file for writing: ' . $this->real_path, 2);
			}
		}

		$fp = fopen($this->real_path, 'w');

		if (!$fp) {
			throw new FileStorage\Exception('Could not open file up for writing: ' . $this->real_path, 2);
		}

		@chmod($this->real_path, $this->file_mode);

		$this->write_p = $fp;

		return $this->write_p;
	}



	/**
	 * Read data from the file at the path.
	 */
	public function get()
	{
		return file_get_contents($this->real_path);
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

		return filesize($this->real_path);
	}



	/**
	 * Not supported
	 */
	public function getMetaData()
	{
		throw new FileStorage\Exception('Operation not supported', 10);
	}



	/**
	 * Get the file pointer for writing to the file.
	 *
	 * @return resource
	 */
	protected function getReadFilePointer()
	{
		if (!$this->fileExists()) {
			throw new FileStorage\Exception('Could not open file up for reading, does not exist: ' . $this->real_path, 3);
		}

		if ($this->read_p !== null) {
			return $this->read_p;
		}

		$fp = @fopen($this->read_p, 'r');

		if (!$fp) {
			throw new FileStorage\Exception('Could not open file up for reading: ' . $this->real_path, 3);
		}

		$this->read_p = $fp;

		return $this->read_p;
	}



	/**
	 * Close the resource once you're done with it.
	 */
	public function release()
	{
		if ($this->write_p !== null) {
			@fclose($this->write_p);
		}

		if ($this->read_p !== null) {
			@fclose($this->read_p);
		}
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
	 * Get the base path
	 *
	 * @return string
	 */
	public function getBasePath()
	{
		return $this->base_path;
	}


	/**
	 * Get the real path of this file on the filesystem.
	 *
	 * @return string
	 */
	public function getRealPath()
	{
		return $this->real_path;
	}
}
