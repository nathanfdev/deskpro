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
use Doctrine\ORM\EntityRepository;

use Orb\Util\Arrays;
use Orb\Util\Strings;

class Article extends EntityRepository
{
	public function getBySlug($slug)
	{
		$id = Strings::extractRegexMatch('#^([0-9]+)#', $slug, 1);
		if (!$id) return null;

		return $this->find($id);
	}


	/**
	 * Get articles waiting for validating
	 * 
	 * @return array
	 */
	public function getValidatingArticle()
	{
		$articles = $this->getEntityManager()->createQuery("
			SELECT a
			FROM DeskPRO:Article a
			WHERE a.hidden_status = ?1
			ORDER BY a.id DESC
		")->setParameter(1, 'validating')->execute();

		return $articles;
	}


	/**
	 * Get drafts, optionally for a specific person
	 *
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @return array
	 */
	public function getDraftArticles(OersonEntity $person = null)
	{
		if ($person) {
			$articles = $this->getEntityManager()->createQuery("
				SELECT a
				FROM DeskPRO:Article a
				WHERE a.hidden_status = ?1 AND a.person = ?2
				ORDER BY a.id DESC
			")->setParameter(1, 'draft')
			  ->setParameter(2, $person)
			  ->execute();
		} else {
			$articles = $this->getEntityManager()->createQuery("
				SELECT a
				FROM DeskPRO:Article a
				WHERE a.hidden_status = ?1
				ORDER BY a.id DESC
			")->setParameter(1, 'draft')->execute();
		}
		
		return $articles;
	}

	

	/**
	 * Get a collection of articles by ID. If $person_context
	 * is supplied, only articles that this person is able to view will be returned.
	 *
	 * @return array
	 */
	public function getByIds(array $ids, PersonEntity $person_context = null)
	{
		if (!$ids) return array();
		
		if ($person_context) {
			//$cat_ids = $person_context->getPermissionsManager()->ArticleCategories->getAllowedCategories();
			//if (!$cat_ids) return array();

			//WHERE a.id IN (" . implode(',', $ids) . " AND a.is_published = true AND cat.id IN (" . implode(',',$cat_ids) . ")

			$articles = $this->getEntityManager()->createQuery("
				SELECT a
				FROM DeskPRO:Article a INDEX BY a.id
				LEFT JOIN a.categories cat
				WHERE a.id IN (" . implode(',', $ids) . ")
				ORDER BY a.id DESC
			")->execute();

		} else {
			$articles = $this->getEntityManager()->createQuery("
				SELECT a
				FROM DeskPRO:Article a INDEX BY a.id
				LEFT JOIN a.categories cat
				WHERE a.id IN (" . implode(',', $ids) . ")
				ORDER BY a.id DESC
			")->execute();
		}

		return $articles;
	}



	/**
	 * Given an array of nodes (usually roots), get the top $num newest articles, and then
	 * sort them into an array keyed by the node IDs.
	 * 
	 * @param  $nodes
	 * @return void
	 */
	public function getNewestInNodes($nodes, $num = 2)
	{
		// This needs to be cached since it's quite costly
		// to get the top results in each category with mysql. And
		// then we have to worry about dupes based on articles being in
		// multiple categories as well. So this is the cleanest way i think,
		// even though its inefficient. But who cares it should be cached.

		$all_articles = array();

		// Articles can be in multiple categories, but we should only be showing them once
		// So we need to not in() them using ID's we fetched in earlier iterations
		$done_articles = array(0);

		foreach ($nodes as $node) {
			$cat_ids = $node->getTreeIds(true);

			$articles = $this->getEntityManager()->createQuery("
				SELECT a
				FROM DeskPRO:Article a INDEX BY a.id
				LEFT JOIN a.categories cat
				WHERE
					cat.id IN (".implode(',',$cat_ids).")
					AND a.id NOT IN (".implode(',',$done_articles).")
					AND a.is_published = true
				GROUP BY a.id
				ORDER BY a.id DESC
			")->setMaxResults($num)
			  ->execute();

			if (count($articles)) {
				$all_articles[$node['id']] = $articles;
				$done_articles = array_merge($done_articles, array_keys($articles));
			}
		}

		return $all_articles;
	}


	
	public function getNewest($num = 10, $node = false)
	{
		if ($node) {
			$cat_ids = $node->getTreeIds(true);
			$articles = $this->getEntityManager()->createQuery("
				SELECT a
				FROM DeskPRO:Article a INDEX BY a.id
				LEFT JOIN a.categories cat
				WHERE a.is_published = true AND cat.id IN (" . implode(',',$cat_ids) . ")
				ORDER BY a.id DESC
			")->setMaxResults($num)->execute();
		} else {
			$articles = $this->getEntityManager()->createQuery("
				SELECT a
				FROM DeskPRO:Article a INDEX BY a.id
				WHERE a.is_published = true
				ORDER BY a.id DESC
			")->setMaxResults($num)->execute();
		}

		return $articles;
	}


	public function getTopRated($num = 10, $node = false)
	{
		if ($node) {
			$cat_ids = $node->getTreeIds(true);
			$articles = $this->getEntityManager()->createQuery("
				SELECT a
				FROM DeskPRO:Article a INDEX BY a.id
				LEFT JOIN a.categories cat
				WHERE a.is_published = true AND cat.id IN (" . implode(',',$cat_ids) . ")
				ORDER BY a.total_rating DESC
			")->setMaxResults($num)->execute();
		} else {
			$articles = $this->getEntityManager()->createQuery("
				SELECT a
				FROM DeskPRO:Article a INDEX BY a.id
				WHERE a.is_published = true
				ORDER BY a.total_rating DESC
			")->setMaxResults($num)->execute();
		}

		return $articles;
	}



	public function getInNode($node)
	{
		return $this->getEntityManager()->createQuery("
			SELECT a
			FROM DeskPRO:Article a
			LEFT JOIN a.categories c
			WHERE c = ?1
			ORDER BY a.id DESC
		")->setParameter(1, $node)->execute();
	}
}