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

use Application\DeskPRO\Entity;
use \Doctrine\ORM\EntityRepository;
use Orb\Util\Util;

class Visitor extends EntityRepository
{
	/**
	 * @return Visitor
	 */
	public function getVisitorFromCode($vis_code)
	{
		$visitor_id = Entity\Session::getIdFromCode($vis_code);
		if (!$visitor_id) {
			return null;
		}

		$visitor = $this->find($visitor_id);
		if (!$visitor OR !$visitor->checkVisitorCode($vis_code)) {
			return null;
		}

		return $visitor;
	}


	/**
	 * @return Visitor
	 */
	public function smartFind($ip_address, $user_agent)
	{
		$datecut = new \DateTime('@' . (time()-86400));
		$datecut = $datecut->format('Y-m-d H:i:s');

		try {
			return $this->getEntityManager()->createQuery("
				SELECT v
				FROM DeskPRO:Visitor v
				WHERE v.date_last > ?1 AND v.ip_address = ?2 AND v.user_agent = ?3
				ORDER BY v.id DESC
			")->setParameter(1, $datecut)
			  ->setParameter(2, $ip_address)
			  ->setParameter(3, $user_agent)
			  ->setMaxResults(1)
			  ->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}
}
