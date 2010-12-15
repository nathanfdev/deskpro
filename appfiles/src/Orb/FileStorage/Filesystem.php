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
 * The Filesystem handler stores files on the local filesystem
 */
class Filesystem extends AbstractStorage
{
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
	 * To use subdirs when generating random filenames
	 * @var bool
	 */
	protected $use_subdirs = true;
	
	/**
	 * @param string $base_path     The base path on the filesystem where the files are located
	 * @param bool   $use_subdirs   Use subdirectories when creating random filepaths
	 * @param int    $dir_mode      The mode newly created directories are created with
	 * @param int    $file_mode     The mode newly created files are created with
	 */
	public function __construct($base_path, $use_subdirs = true, $dir_mode = 0755, $file_mode = 0744)
	{
		$this->base_path = rtrim(str_replace('\\', '/', $base_path), '/');

		$this->use_subdirs = $use_subdirs;

		$this->dir_mode  = $dir_mode;
		$this->file_mode = $file_mode;
	}


	
	/**
	 * Gets a file descriptor object for a certain path. Note that this
	 * path might not exist.
	 *
	 * @return Orb\FileStorage\FileDescriptor\Filesystem
	 */
	public function getFileDescriptor($path)
	{
		$desc = new FileDescriptor\Filesystem($path, $this->base_path, $this->dir_mode, $this->file_mode);
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
	 * Get the base path
	 *
	 * @return string
	 */
	public function getBasePath()
	{
		return $this->base_path;
	}
}