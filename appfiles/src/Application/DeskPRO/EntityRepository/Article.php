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

class Article extends EntityRepository
{
	/**
	 * Given an array of nodes (usually roots), get the top $num newest articles, and then
	 * sort them into an array keyed by the node IDs.
	 * 
	 * @param  $nodes
	 * @return void
	 */
	public function getNewestInNodes($nodes, $num = 2)
	{
		// Mysql doesnt have a great way to do this. One way is to use
		// variables and inner queries, another way is to do this and just union
		// the brains out of things. Either way this result'll need to be cached

		// Using native SQL to get ID's first, and then pass it to doctrine to hydrate.
		// Again, not very performant, but it's cleaner. Alternative is to run the native
		// query and then use Doctrine's ResultSetMapping. But who cares since we're caching anyway

		// An array of childid=>top
		// We'll use after to sort articles from deep in the hierarchy
		// under their top-most parent
		$children_to_top = array();

		$sql = array();
		foreach ($nodes as $node) {
			$child_ids = $node->getTreeIds(true);

			$children_to_top = array_merge($children_to_top, array_fill_keys($child_ids, $node['id']));
			
			$sql[] = "
				SELECT id
				FROM articles
				LEFT JOIN article_to_categories map ON (map.article_id = articles.id)
				WHERE
					map.category_id IN (" . implode(',', $child_ids) . ")
					AND articles.is_published = 1
				ORDER BY articles.id
				DESC LIMIT $num
			";
		}

		$sql = "(" . implode(') UNION (', $sql) . ")";
		$ids = App::getDb()->fetchAllCol($sql);

		if (!$ids) {
			return array();
		}

		$articles_all = $this->getEntityManager()->createQuery("
			SELECT a
			FROM DeskPRO:Article a
			WHERE a.id IN(?1)
			ORDER BY a.id DESC
		")->setParameter(1, implode(',', $ids))->execute();

		// Group them into their nodes
		$articles = array();
		foreach ($articles_all as $art) {
			$top_cat = $children_to_top[$art['category']['id']];
			if (!isset($articles[$top_cat])) $articles[$top_cat] = array();

			$articles[$top_cat][] = $art;
		}

		return $articles;
	}



	public function getArticlesInCategory($category)
	{
		return $this->getEntityManager()->createQuery("
			SELECT a
			FROM DeskPRO:Article a
			LEFT JOIN a.categories c
			WHERE c = ?1
			ORDER BY a.id DESC
		")->setParameter(1, $category)->execute();
	}
}