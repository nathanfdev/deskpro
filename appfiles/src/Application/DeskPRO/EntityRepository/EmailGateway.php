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

class EmailGateway extends EntityRepository
{
	protected $_gateway_names = null;

	public function getGatewayNames(array $for_ids = null)
	{
		if ($this->_gateway_names === null) {
			$this->_gateway_names = array();

			$recs = App::getDb()->fetchAll("
				SELECT id, name, address
				FROM email_gateways
				ORDER BY name DESC
			");
			foreach ($recs as $rec) {
				$this->_gateway_names[$rec['id']] = "{$rec['name']} <{$rec['address']}>";
			}
		}

		if ($for_ids) {
			$names = array();
			foreach ($for_ids as $id) {
				if (isset($this->_gateway_names[$id])) {
					$names[$id] = $this->_gateway_names[$id];
				}
			}

			return $names;
		}

		return $this->_gateway_names;
	}

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
