<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;
use \Doctrine\ORM\EntityRepository;

class ApiKey extends EntityRepository
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
}