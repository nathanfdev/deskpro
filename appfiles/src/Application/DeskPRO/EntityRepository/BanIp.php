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

use Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class BanIp extends EntityRepository
{
	/**
	 * Get a list of IPs suitable for display
	 */
	public function getList()
	{
		$list = App::getDb()->fetchAllCol("
			SELECT banned_ip
			FROM ban_ips
			ORDER BY ip_start ASC
		");

		return $list;
	}
}
