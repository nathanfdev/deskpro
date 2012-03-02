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
 * @subpackage FileStorage
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
