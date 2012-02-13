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
 * A storage handler is a method for storing and/or retrieving files.
 * A handler is paired with a FileDescriptor class where most of the work is
 * actually done. A FileDescriptor knows how to create files, get contents, etc,
 * using the protocol it was desgiend for.
 *
 * These storage handlers are usually just factories for file descriptors.
 */
abstract class AbstractStorage
{
	protected $_options = array();



	/**
	 * Gets a file descriptor object for a certain path. Note that this
	 * path might not exist.
	 *
	 * @return \Orb\FileStorage\FileDescriptor\AbstractFileDescriptor
	 */
	abstract public function getFileDescriptor($path);



	/**
	 * Get a file descriptor object with a new, randomly generated path. This is
	 * useful for storing things like attachments, where the filename doesn't matter
	 * because the real name is stored somewhere else.
	 *
	 * @return \Orb\FileStorage\FileDescriptor\AbstractFileDescriptor
	 */
	public function createRandomPath()
	{
		do {
			$path = date('Ymd') . '-' . Util::uuid4();
			$desc = $this->getFileDescriptor($path);
		} while($desc->exists());

		return $desc;
	}
}
