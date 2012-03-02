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
