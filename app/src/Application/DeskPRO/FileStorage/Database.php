<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage FileStorage
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\FileStorage;

use Application\DeskPRO\App;

use Orb\Util\Util;

/**
 * This handler stores files in teh database as blob parts.
 *
 * We aren't using entities here, just the raw lowlevel db.
 */
class Database extends \Orb\FileStorage\AbstractStorage
{
	/**
	 * Database connection to use
	 * @var Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	public function __construct(\Application\DeskPRO\DBAL\Connection $db)
	{
		if (!$db) {
			$db = App::getDb();
		}

		$this->db = $db;
	}



	/**
	 * Gets a file descriptor object for a certain path. Note that this
	 * path might not exist.
	 *
	 * @return Orb\FileStorage\FileDescriptor\Filesystem
	 */
	public function getFileDescriptor($blob_id)
	{
		$desc = new FileDescriptor\Database($blob_id, $this->db);
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
		$desc = $this->getFileDescriptor(null);
		return $desc;
	}
}
