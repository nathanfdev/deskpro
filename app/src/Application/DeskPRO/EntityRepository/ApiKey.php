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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;
use Doctrine\ORM\EntityRepository;
use Application\DeskPRO\App;

class ApiKey extends AbstractEntityRepository
{
	/**
	 * Find an API key based off of a key string. A key string is: "id:code"
	 * 
	 * @param string $key_str
	 * @return ApiKey
	 */
	public function findByKeyString($key_string)
	{
		if (strpos($key_string, ':') === false) return null;
		
		list ($id, $code) = explode(':', $key_string, 2);

		$apikey = $this->find($id);
		if (!$apikey) return null;
		if ($apikey['code'] != $code) return null;

		return $apikey;
	}

	public function getAllApiKeys()
	{
		return $this->_em->createQuery('
			SELECT k
			FROM DeskPRO:ApiKey k
			LEFT JOIN k.person p
			ORDER BY p.name
		')->execute();
	}

	public function getApiKeyTitles(array $ids = null)
	{
		$output = array();
		foreach ($this->getAllApiKeys() AS $key) {
			if ($ids === null || in_array($key->id, $ids)) {
				$output[$key->id] = ($key->person ? $key->person->display_name : 'Super User')
					. ($key->note ? " ($key->note)" : '');
			}
		}

		return $output;
	}

	public function countApiKeys()
	{
		return App::getDb()->fetchColumn('
			SELECT COUNT(*)
			FROM api_keys
		');
	}
}
