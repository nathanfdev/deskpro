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

class EmailGatewayAddress extends EntityRepository
{
	public function getOptions()
	{
		$opts = $this->getEntityManager()->getConnection()->fetchAllKeyValue("
			SELECT id, match_pattern
			FROM email_gateway_addresses
		");

		return $opts;
	}
}
