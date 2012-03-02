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

/**
 * A file descriptor knows how to speak a certain protocol to do file-related
 * tasks.
 *
 * Every file descriptor has some way to map a "path" to a file. This path is called
 * the "path description." It usually isn't a real path, but it contains the required
 * information (in combination with any configuration elements) to properly locate a file.
 */
abstract class AbstractFileDescriptor
{
	const METADATA_CONTENT_TYPE = 'content_type';
	const METADATA_FILENAME = 'filename';
	const METADATA_FILESIZE = 'filesize';
	const METADATA_FILEHASH = 'filehash';

	/**
	 * Does the file exist?
	 *
	 * @return bool
	 */
	abstract public function exists();



	/**
	 * Delete the file at the path.
	 *
	 * @return bool
	 */
	abstract public function delete();


	/**
	 * Write data to the file at the path. This should overwrite current data
	 * if the path exists.
	 *
	 * @param string $data The data to write
	 * @param array  $meta Any metadata such as mimetype (not all adapters may support this)
	 */
	abstract public function write($data, $meta = null);



	/**
	 * Read data from the file at the path.
	 *
	 * @return string
	 */
	abstract public function get();



	/**
	 * Get the data and write it to a file.
	 *
	 * Returns a file resource.
	 *
	 * @return resource $fp_write
	 */
	public function getToFile($fp_write = null)
	{
		if (!is_resource($fp_write)) {
			throw new Orb_AttachSource_Exception('Could not open file up for writing (to write from source): ' . $fp_path, 5);
		}

		fwrite($fp_write, $this->get());
	}



	/**
	 * Get the size of the file in bytes.
	 *
	 * Returnes NULL when this information cannot be determined (i.e., an adapter
	 * does not support it).
	 *
	 * @return int
	 */
	abstract public function getLength();



	/**
	 * Get any metadata stored with the file. Some adapters may not support this.
	 *
	 * @return array
	 */
	abstract public function getMetaData();



	/**
	 * Write data from a file pointer.
	 *
	 * @param resource $fp_read The file pointer
	 * @param array $meta Any metadata (Content-Type is sometimes important)
	 */
	public function writeFromFile($fp_read, $meta = null)
	{
		$data = stream_get_contents($fp_read);
		if (!isset($meta[self::METADATA_FILEHASH])) {
			$meta[self::METADATA_FILEHASH] = sha1($data);
		}
		$this->write($data, $meta);
	}



	/**
	 * Output the data of the file (i.e., a user is downloading it.)
	 */
	public function output()
	{
		$buf = $this->get(null);
		echo $buf;
	}



	/**
	 * After finished working with the descriptor, release it which releases
	 * any file poitners etc. This is automatically called on destruct.
	 */
	abstract public function release();



	/**
	 * If there is a direct link to the resource (i.e., via a CDN), then return it.
	 * If this source does not support direct linking, then return null.
	 *
	 * @return string
	 */
	public function getDirectLink()
	{
		return null;
	}



	/**
	 * Get the path that can be used to re-create this descriptor.
	 *
	 * @return string
	 */
	abstract public function getPath();



	public function __destruct()
	{
		$this->release();
	}
}
