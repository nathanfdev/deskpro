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

use \Orb\Util\Arrays;

use \Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;

class EmailGateway extends EntityRepository
{
	public function getGatewayFromAddress($address)
	{
		$address = (array)$address;

		foreach ($address as $addr) {
			try {
				$gateway = $this->getEntityManager()->createQuery("
					SELECT g
					FROM DeskPRO:EmailGateway g
					WHERE g.address = ?1
				")->setParameter(1, $addr)->setMaxResults(1)->getSingleResult();

				if ($gateway) {
					return $gateway;
				}
			} catch (\Doctrine\ORM\NoResultException $e) {}
		}


		return null;
	}
}