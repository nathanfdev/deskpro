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
use Application\DeskPRO\Entity\Person as PersonEntity;

use Orb\Util\Arrays;
use Orb\Util\Strings;

class News extends AbstractEntityRepository
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
	public function getByIdsWithContext(array $ids, PersonEntity $person_context = null)
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

	public function getByResultIds(array $ids)
	{
		if (!$ids) return array();

		$unsorted_news = $this->getEntityManager()->createQuery("
			SELECT n
			FROM DeskPRO:News n INDEX BY n.id
			WHERE n.id IN (" . implode(',', $ids) . ")
			ORDER BY n.id DESC
		")->execute();

		$news = array();

		foreach ($ids as $id) {
			if (isset($unsorted_news[$id])) {
				$news[$id] = $unsorted_news[$id];
			}
		}

		return $news;
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


	public function getNewest($num = 10, $node = false)
	{
		if ($node) {
			$cat_ids = $node->getTreeIds(true);
			$articles = $this->getEntityManager()->createQuery("
				SELECT n
				FROM DeskPRO:News n INDEX BY n.id
				WHERE n.status = 'published' AND n.category IN (" . implode(',',$cat_ids) . ")
				ORDER BY n.id DESC
			")->setMaxResults($num)->execute();
		} else {
			$articles = $this->getEntityManager()->createQuery("
				SELECT n
				FROM DeskPRO:News n INDEX BY n.id
				WHERE n.status = 'published'
				ORDER BY n.id DESC
			")->setMaxResults($num)->execute();
		}

		return $articles;
	}
}
