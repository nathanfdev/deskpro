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

class EmailTransport extends EntityRepository
{
	public function findAll()
	{
		return $this->getEntityManager()->createQuery("
			SELECT t
			FROM DeskPRO:EmailTransport t
			ORDER BY t.run_order ASC
		")->execute();
	}

	public function findTransportForAddress($address)
	{
		$transports = $this->findAll();

		foreach ($transports as $tr) {
			if ($tr->doesMatchFromAddress($address)) {
				return $tr;
			}
		}

		return null;
	}
}
