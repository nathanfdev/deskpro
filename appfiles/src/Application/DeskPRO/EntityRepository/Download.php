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

use \Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;

use \Orb\Util\Arrays;

class Download extends EntityRepository
{
	public function getNewest($num = 10, $node = false)
	{
		if ($node) {
			$downloads = $this->getEntityManager()->createQuery("
				SELECT d
				FROM DeskPRO:Download d
				WHERE d.category = ?1
				ORDER BY d.id DESC
			")->setParameter(1, $node)->setMaxResults($num)->execute();
		} else {
			$downloads = $this->getEntityManager()->createQuery("
				SELECT d
				FROM DeskPRO:Download d
				ORDER BY d.id DESC
			")->setMaxResults($num)->execute();
		}

		return $downloads;
	}


	public function getPopular($num = 10, $node = false)
	{
		if ($node) {
			$downloads = $this->getEntityManager()->createQuery("
				SELECT d
				FROM DeskPRO:Download d
				WHERE d.category = ?1
				ORDER BY d.num_downloads DESC
			")->setParameter(1, $node)->setMaxResults($num)->execute();
		} else {
			$downloads = $this->getEntityManager()->createQuery("
				SELECT d
				FROM DeskPRO:Download d
				ORDER BY d.num_downloads DESC
			")->setMaxResults($num)->execute();
		}

		return $downloads;
	}



	public function getInNode($node)
	{
		return $this->getEntityManager()->createQuery("
			SELECT d
			FROM DeskPRO:Download d
			WHERE d.category = ?1
			ORDER BY d.title DESC
		")->setParameter(1, $node)->execute();
	}
}