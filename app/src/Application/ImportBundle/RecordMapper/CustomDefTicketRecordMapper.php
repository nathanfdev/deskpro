<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @package Importer
 */

namespace Application\ImportBundle\RecordMapper;

use Doctrine\DBAL\Connection;

class CustomDefTicketRecordMapper extends CommonRecordMapper
{
	protected $cache;
	
	/**
	 * Returns person ID given a title.
	 *
	 * @param mixed $title
	 * @return int|null
	 */
	public function findIdFromValue($title)
	{
		$dataArray = $this->fetch($title);
				
		return isset($dataArray['id']) ? $dataArray['id'] : null;
	}
	
	/**
	 * Checks if the give person is an agent
	 * 
	 * @param string $title The title to check by
	 * @return bool True if the given user is an agent and false otherwise
	 */
	public function fetchHandlerClass($title)
	{
		$dataArray = $this->fetch($title);
		
		return $dataArray['handler_class'];
	}
	
	protected function fetch($title)
	{
		$query = 'SELECT id, handler_class FROM custom_def_ticket WHERE title = ?';

		if (!isset($this->cache[$title])) {
			$result = $this->db->fetchAll($query, array($title));
			
			if (!$result || !isset($result[0])) {
				return null;
			}
			
			while (count($this->cache) >= 5000) {
				array_shift($this->cache);
			}
			
			$this->cache[$title] = $result[0];
		}
		
		return $this->cache[$title];
	}
}