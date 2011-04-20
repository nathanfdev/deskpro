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

class News extends EntityRepository
{
	public function getBySlug($slug)
	{
		$id = Strings::extractRegexMatch('#^([0-9]+)#', $slug, 1);
		if (!$id) return null;

		return $this->find($id);
	}

	/**
	 * Get a collection of posts by ID. If $person_context
	 * is supplied, only articles that this person is able to view will be returned.
	 *
	 * @return array
	 */
	public function getByIds(array $ids, Person $person_context = null)
	{
		if (!$ids) return array();
		
		if ($person_context) {
			$posts = $this->getEntityManager()->createQuery("
				SELECT p
				FROM DeskPRO:News p INDEX BY p.id
				WHERE p.id IN (" . implode(',', $ids) . ")
				ORDER BY p.id DESC
			")->execute();
		} else {
			$posts = $this->getEntityManager()->createQuery("
				SELECT p
				FROM DeskPRO:News p INDEX BY p.id
				WHERE p.id IN (" . implode(',', $ids) . ")
				ORDER BY p.id DESC
			")->execute();
		}

		return $posts;
	}
	
	public function getNews($node, $num = 20)
	{
		if ($node) {
			$news = $this->getEntityManager()->createQuery("
				SELECT n
				FROM DeskPRO:News n
				WHERE n.category = ?1
				ORDER BY n.id DESC
			")->setParameter(1, $node)->setMaxResults($num)->execute();
		} else {
			$news = $this->getEntityManager()->createQuery("
				SELECT n
				FROM DeskPRO:News n
				ORDER BY n.id DESC
			")->setMaxResults($num)->execute();
		}

		return $news;
	}
}