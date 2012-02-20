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

use Orb\Util\Arrays;

use Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;

class UserRule extends EntityRepository
{
	/**
	 * Find all matching rules on an email address
	 *
	 * @param $email_address
	 * @return array
	 */
	public function getMatching($email_address)
	{
		$all = $this->findAll();

		$matching = array();

		foreach ($all as $p) {
			if ($p->isEmailMatch($email_address)) {
				$matching[] = $p;
			}
		}

		return $matching;
	}
}
