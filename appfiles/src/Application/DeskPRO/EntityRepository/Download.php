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
use \Application\DeskPRO\Entity\Person;
use \Doctrine\ORM\EntityRepository;

use \Orb\Util\Arrays;
use \Orb\Util\Strings;

class Download extends EntityRepository
{
	public function getBySlug($slug)
	{
		$id = Strings::extractRegexMatch('#^([0-9]+)#', $slug, 1);
		if (!$id) return null;

		return $this->find($id);
	}

	/**
	 * Get a collection of downloads by ID. If $person_context
	 * is supplied, only articles that this person is able to view will be returned.
	 *
	 * @return array
	 */
	public function getByIds(array $ids, Person $person_context = null)
	{
		if ($person_context) {
			$downloads = $this->getEntityManager()->createQuery("
				SELECT d
				FROM DeskPRO:Download d INDEX BY d.id
				WHERE d.id IN (" . implode(',', $ids) . ")
				ORDER BY d.id DESC
			")->execute();
		} else {
			$downloads = $this->getEntityManager()->createQuery("
				SELECT d
				FROM DeskPRO:Download d INDEX BY d.id
				WHERE d.id IN (" . implode(',', $ids) . ")
				ORDER BY d.id DESC
			")->execute();
		}

		return $downloads;
	}

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