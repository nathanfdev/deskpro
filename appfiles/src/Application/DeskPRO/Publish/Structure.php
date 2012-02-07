<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Publish
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Publish;

use	Doctrine\ORM\EntityManager;
use Orb\Doctrine\Common\Cache\PreloadedMysqlCache;
use Orb\Util\Arrays;

use Application\DeskPRO\Searcher\ArticleSearch;
use Application\DeskPRO\Searcher\IdeaSearch;
use Application\DeskPRO\Searcher\DownloadSearch;
use Application\DeskPRO\Searcher\NewsSearch;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Idea;
use Application\DeskPRO\Entity\IdeaCategory;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;

class Structure
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Orb\Doctrine\Common\Cache\PreloadedMysqlCache
	 */
	protected $cache = null;

	/**
	 * @var array
	 */
	protected $category_data = array();

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em, PreloadedMysqlCache $cache)
	{
		$this->em = $em;
		$this->cache = $cache;
	}



	####################################################################################################################
	# Article Fetchers
	####################################################################################################################

	/**
	 * Get all categories in proper displaying order. You can also determine hierarchy by 'depth'.
	 *
	 * @return array
	 */
	public function getArticleCategories()
	{
		$ent = 'DeskPRO:ArticleCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['all'];
	}


	/**
	 * @return mixed
	 */
	public function getArticleRootCategories()
	{
		$ent = 'DeskPRO:ArticleCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['hierarchy'];
	}


	/**
	 * Get an array of all category IDs
	 *
	 * @return array
	 */
	public function getArticleCategoryIds()
	{
		$ent = 'DeskPRO:ArticleCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['ids'];
	}


	/**
	 * @param $slug
	 * @return
	 */
	public function getArticleCategory($id)
	{
		$ent = 'DeskPRO:ArticleCategory';
		$this->loadCategories($ent);

		if (!isset($this->category_data[$ent]['all'][$id])) {
			throw new \InvalidArgumentException("Invalid category id `$id`");
		}

		return $this->category_data[$ent]['all'][$id];
	}


	/**
	 * @param $id
	 * @return bool
	 */
	public function hasArticleCategory($id)
	{
		$ent = 'DeskPRO:ArticleCategory';
		$this->loadCategories($ent);

		return isset($this->category_data[$ent]['all'][$id]);
	}


	/**
	 * Get an array of id=>name
	 *
	 * @param string $sep
	 * @param bool $include_tops
	 * @return array
	 */
	public function getArticleCategoryNames($sep = ' > ', $include_tops = true)
	{
		$ent = 'DeskPRO:ArticleCategory';
		$this->loadCategories($ent);
		return $this->_getFullCategoryNames(array(), $this->category_data[$ent]['hierarchy'], $sep, $include_tops);
	}


	/**
	 * @return \Orb\Util\HierarchyStructure
	 */
	public function getArticleCategoryHelper()
	{
		$ent = 'DeskPRO:ArticleCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['helper'];
	}


	/**
	 * @param \Application\DeskPRO\Entity\Person|null $person_context
	 * @return array
	 */
	public function getArticleCategoryCounts(Person $person_context = null)
	{
		$ent = 'DeskPRO:ArticleCategory';
		$id = 'categories.counts.' . $ent;
		$this->loadCategories($ent);

		if ($counts = $this->cache->fetch($id)) {
			return $counts;
		}

		$counts = array('0' => 0, '0_total' => 0);

		foreach ($this->category_data[$ent]['ids'] as $cid) {
			$searcher = new ArticleSearch();
			$searcher->setPersonContext($person_context);
			$searcher->addTerm(ArticleSearch::TERM_CATEGORY_SPECIFIC, 'is', $cid);
			$searcher->addTerm(ArticleSearch::TERM_STATUS, 'is', 'published');

			$counts[$cid] = $searcher->getCount();
		}

		$counts = $this->_getTotalCounts($counts, $this->category_data[$ent]['all'], $this->category_data[$ent]['helper']);

		$this->cache->save($id, $counts);

		return $counts;
	}

	####################################################################################################################
	# Idea Fetchers
	####################################################################################################################

	/**
	 * Get all categories in proper displaying order. You can also determine hierarchy by 'depth'.
	 *
	 * @return array
	 */
	public function getIdeaCategories()
	{
		$ent = 'DeskPRO:IdeaCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['all'];
	}


	/**
	 * @return mixed
	 */
	public function getIdeaRootCategories()
	{
		$ent = 'DeskPRO:IdeaCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['hierarchy'];
	}


	/**
	 * Get an array of all category IDs
	 *
	 * @return array
	 */
	public function getIdeaCategoryIds()
	{
		$ent = 'DeskPRO:IdeaCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['ids'];
	}


	/**
	 * @param $slug
	 * @return
	 */
	public function getIdeaCategory($id)
	{
		$ent = 'DeskPRO:IdeaCategory';
		$this->loadCategories($ent);

		if (!isset($this->category_data[$ent]['all'][$id])) {
			throw new \InvalidArgumentException("Invalid category id `$id`");
		}

		return $this->category_data[$ent]['all'][$id];
	}


	/**
	 * @param $id
	 * @return bool
	 */
	public function hasIdeaCategory($id)
	{
		$ent = 'DeskPRO:IdeaCategory';
		$this->loadCategories($ent);

		return isset($this->category_data[$ent]['all'][$id]);
	}


	/**
	 * Get an array of id=>name
	 *
	 * @param string $sep
	 * @param bool $include_tops
	 * @return array
	 */
	public function getIdeaCategoryNames($sep = ' > ', $include_tops = true)
	{
		$ent = 'DeskPRO:IdeaCategory';
		$this->loadCategories($ent);
		return $this->_getFullCategoryNames(array(), $this->category_data[$ent]['hierarchy'], $sep, $include_tops);
	}


	/**
	 * @return \Orb\Util\HierarchyStructure
	 */
	public function getIdeaCategoryHelper()
	{
		$ent = 'DeskPRO:IdeaCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['helper'];
	}


	/**
	 * @param \Application\DeskPRO\Entity\Person|null $person_context
	 * @return array
	 */
	public function getIdeaCategoryCounts(Person $person_context = null)
	{
		$ent = 'DeskPRO:IdeaCategory';
		$id = 'categories.counts.' . $ent;
		$this->loadCategories($ent);

		if ($counts = $this->cache->fetch($id)) {
			return $counts;
		}

		$counts = array(0 => array('popular' => 0, 'new' => 0, 'active' => 0, 'closed' => 0));
		foreach ($this->getIdeaCategories() as $c) {

			$cat_counts = array();

			$searcher = new IdeaSearch();
			$searcher->setPersonContext($person_context);
			$searcher->addTerm(IdeaSearch::TERM_CATEGORY_SPECIFIC, 'is', $c['id']);
			$searcher->addTerm(IdeaSearch::TERM_STATUS, 'is', Idea::STATUS_NEW);
			$cat_counts['new'] = $searcher->getCount();

			$searcher = new IdeaSearch();
			$searcher->setPersonContext($person_context);
			$searcher->addTerm(IdeaSearch::TERM_CATEGORY_SPECIFIC, 'is', $c['id']);
			$searcher->addTerm(IdeaSearch::TERM_STATUS, 'is', Idea::STATUS_ACTIVE);
			$cat_counts['active'] = $searcher->getCount();

			$searcher = new IdeaSearch();
			$searcher->setPersonContext($person_context);
			$searcher->addTerm(IdeaSearch::TERM_CATEGORY_SPECIFIC, 'is', $c['id']);
			$searcher->addTerm(IdeaSearch::TERM_STATUS, 'is', Idea::STATUS_CLOSED);
			$cat_counts['closed'] = $searcher->getCount();

			$cat_counts['all'] = array_sum($cat_counts);


			$counts[$c['id']] = $cat_counts;

			// 0 is sum of all root nodes
			if (!$c['depth']) {
				$counts[0]['new']     += $counts[$c['id']]['new'];
				$counts[0]['active']  += $counts[$c['id']]['active'];
				$counts[0]['closed']  += $counts[$c['id']]['closed'];
			}
		}

		$counts[0]['all'] = array_sum($counts[0]);

		$this->cache->save($id, $counts);

		return $counts;
	}


	####################################################################################################################
	# Download Fetchers
	####################################################################################################################

	/**
	 * Get all categories in proper displaying order. You can also determine hierarchy by 'depth'.
	 *
	 * @return array
	 */
	public function getDownloadCategories()
	{
		$ent = 'DeskPRO:DownloadCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['all'];
	}


	/**
	 * @return mixed
	 */
	public function getDownloadRootCategories()
	{
		$ent = 'DeskPRO:DownloadCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['hierarchy'];
	}


	/**
	 * Get an array of all category IDs
	 *
	 * @return array
	 */
	public function getDownloadCategoryIds()
	{
		$ent = 'DeskPRO:DownloadCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['ids'];
	}


	/**
	 * @param $slug
	 * @return
	 */
	public function getDownloadCategory($id)
	{
		$ent = 'DeskPRO:DownloadCategory';
		$this->loadCategories($ent);

		if (!isset($this->category_data[$ent]['all'][$id])) {
			throw new \InvalidArgumentException("Invalid category id `$id`");
		}

		return $this->category_data[$ent]['all'][$id];
	}


	/**
	 * @param $id
	 * @return bool
	 */
	public function hasDownloadCategory($id)
	{
		$ent = 'DeskPRO:DownloadCategory';
		$this->loadCategories($ent);

		return isset($this->category_data[$ent]['all'][$id]);
	}


	/**
	 * Get an array of id=>name
	 *
	 * @param string $sep
	 * @param bool $include_tops
	 * @return array
	 */
	public function getDownloadCategoryNames($sep = ' > ', $include_tops = true)
	{
		$ent = 'DeskPRO:DownloadCategory';
		$this->loadCategories($ent);
		return $this->_getFullCategoryNames(array(), $this->category_data[$ent]['hierarchy'], $sep, $include_tops);
	}


	/**
	 * @return \Orb\Util\HierarchyStructure
	 */
	public function getDownloadCategoryHelper()
	{
		$ent = 'DeskPRO:DownloadCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['helper'];
	}


	/**
	 * @param \Application\DeskPRO\Entity\Person|null $person_context
	 * @return array
	 */
	public function getDownloadCategoryCounts(Person $person_context = null)
	{
		$ent = 'DeskPRO:DownloadCategory';
		$id = 'categories.counts.' . $ent;
		$this->loadCategories($ent);

		if ($counts = $this->cache->fetch($id)) {
			return $counts;
		}

		$counts = array('0' => 0, '0_total' => 0);

		foreach ($this->getDownloadCategories() as $cat) {
			$searcher = new DownloadSearch();
			$searcher->setPersonContext($person_context);
			$searcher->addTerm(DownloadSearch::TERM_CATEGORY_SPECIFIC, 'is', $cat->id);
			$searcher->addTerm(DownloadSearch::TERM_STATUS, 'is', 'published');

			$counts[$cat->id] = $searcher->getCount();
		}

		$counts = $this->_getTotalCounts($counts, $this->getDownloadCategories(), $this->getDownloadCategoryHelper());

		$this->cache->save($id, $counts);

		return $counts;
	}


	####################################################################################################################
	# News Fetchers
	####################################################################################################################

	/**
	 * Get all categories in proper displaying order. You can also determine hierarchy by 'depth'.
	 *
	 * @return array
	 */
	public function getNewsCategories()
	{
		$ent = 'DeskPRO:NewsCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['all'];
	}


	/**
	 * @return mixed
	 */
	public function getNewsRootCategories()
	{
		$ent = 'DeskPRO:NewsCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['hierarchy'];
	}


	/**
	 * Get an array of all category IDs
	 *
	 * @return array
	 */
	public function getNewsCategoryIds()
	{
		$ent = 'DeskPRO:NewsCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['ids'];
	}


	/**
	 * @param $slug
	 * @return
	 */
	public function getNewsCategory($id)
	{
		$ent = 'DeskPRO:NewsCategory';
		$this->loadCategories($ent);

		if (!isset($this->category_data[$ent]['all'][$id])) {
			throw new \InvalidArgumentException("Invalid category id `$id`");
		}

		return $this->category_data[$ent]['all'][$id];
	}


	/**
	 * @param $id
	 * @return bool
	 */
	public function hasNewsCategory($id)
	{
		$ent = 'DeskPRO:NewsCategory';
		$this->loadCategories($ent);

		return isset($this->category_data[$ent]['all'][$id]);
	}


	/**
	 * Get an array of id=>name
	 *
	 * @param string $sep
	 * @param bool $include_tops
	 * @return array
	 */
	public function getNewsCategoryNames($sep = ' > ', $include_tops = true)
	{
		$ent = 'DeskPRO:NewsCategory';
		$this->loadCategories($ent);
		return $this->_getFullCategoryNames(array(), $this->category_data[$ent]['hierarchy'], $sep, $include_tops);
	}


	/**
	 * @return \Orb\Util\HierarchyStructure
	 */
	public function getNewsCategoryHelper()
	{
		$ent = 'DeskPRO:NewsCategory';
		$this->loadCategories($ent);
		return $this->category_data[$ent]['helper'];
	}


	/**
	 * @param \Application\DeskPRO\Entity\Person|null $person_context
	 * @return array
	 */
	public function getNewsCategoryCounts(Person $person_context = null)
	{
		$ent = 'DeskPRO:NewsCategory';
		$id = 'categories.counts.' . $ent;
		$this->loadCategories($ent);

		if ($counts = $this->cache->fetch($id)) {
			return $counts;
		}

		$counts = array('0' => 0, '0_total' => 0);

		foreach ($this->getNewsCategories() as $c) {
			$searcher = new NewsSearch();
			$searcher->setPersonContext($person_context);
			$searcher->addTerm(NewsSearch::TERM_CATEGORY_SPECIFIC, 'is', $c['id']);
			$searcher->addTerm(NewsSearch::TERM_STATUS, 'is', 'published');

			$counts[$c['id']] = $searcher->getCount();

			$counts['0_total'] += $counts[$c['id']];
		}

		$structure = $this;
		$fn_count = function($node) use (&$counts, $structure, &$fn_count) {
			$total = 0;
			foreach ($structure->getNewsCategoryHelper()->getChildren($node, true) as $c) {
				// We already have the single count
				$total += $counts[$c['id']];

				// Now add up all its subs
				$total += $fn_count($c);
			}

			if ($node) {
				$counts[$node['id'] . '_total'] = $total;
			}

			return $total;
		};

		$fn_count(null);

		$this->cache->save($id, $counts);

		return $counts;
	}


	####################################################################################################################
	# Helpers
	####################################################################################################################

	public function getCategoryHelperForCategory($obj)
	{
		if ($obj instanceof ArticleCategory) {
			return $this->getArticleCategoryHelper();
		} elseif ($obj instanceof IdeaCategory) {
			return $this->getIdeaCategoryHelper();
		} elseif ($obj instanceof DownloadCategory) {
			return $this->getDownloadCategoryHelper();
		} elseif ($obj instanceof NewsCategory) {
			return $this->getNewsCategoryHelper();
		}

		return null;
	}

	/**
	 * Helper used when generating a full names list
	 *
	 * @param $basenames
	 * @param $cats
	 * @param $sep
	 * @param $include_tops
	 * @return array
	 */
	protected function _getFullCategoryNames($basenames, $cats, $sep, $include_tops)
	{
		$names = array();

		foreach ($cats as $k => $cat) {
			$name = $basenames;
			$name[] = $cat['title'];

			if (!$cat['children'] OR $include_tops) {
				$names[$k] = implode($sep, $name);
			}
			if ($cat['children']) {
				$names = Arrays::mergeAssoc($names, $this->_getFullCategoryNames($name, $cat['children'], $sep, $include_tops));
			}
		}

		return $names;
	}

	protected function _getTotalCounts(array $counts, $cats, \Orb\Util\HierarchyStructure $h)
	{
		$counts['0_total'] = 0;

		foreach ($cats as $cat) {
			$total = 0;
			$c_id = $cat['id'];
			if (isset($counts[$c_id])) {
				$total = $counts[$c_id];
			}

			foreach ($h->getChildrenIds($cat, false) as $child_id) {
				if (isset($counts[$child_id])) {
					$total += $counts[$child_id];
				}
			}

			$counts["{$c_id}_total"] = $total;
			$counts['0_total'] += $total;
		}

		return $counts;
	}



	/**
	 * Loads category data from the database
	 *
	 * @param $ent
	 * @return mixed
	 */
	protected function loadCategories($ent)
	{
		if (isset($this->category_data[$ent])) {
			return;
		}

		$this->cache->preloadPrefix('categories');

		$cats = $this->em->createQuery("
			SELECT cat
			FROM $ent cat INDEX BY cat.id
			ORDER BY cat.display_order
		")->setFetchMode($ent, 'children', 'EAGER')
		  ->setFetchMode($ent, 'parent', 'EAGER')
		  ->setResultCacheDriver($this->cache)->setResultCacheId('categories.recs.'.$ent)
		  ->execute();

		foreach ($cats as $c) {
			$c->structure_helper = $this;
		}

		$this->category_data[$ent] = array();
		$this->category_data[$ent]['all'] = $cats;
		$this->category_data[$ent]['ids'] = array_keys($cats);

		$maps = $this->cache->fetch('categories.maps.' . $ent);
		if (!$maps) {
			$parent_map = $this->em->getConnection()->fetchAll("
				SELECT id, COALESCE(parent_id, 0) AS parent_id
				FROM " . $this->em->getRepository($ent)->getTableName() . "
				ORDER BY display_order DESC
			");
			$parent_map = Arrays::keyFromData($parent_map, 'id', 'parent_id');

			$child_map = array(0 => array());
			foreach ($parent_map as $parent_id => $child_id) {
				if ($parent_id == 0) {
					$child_map[0][] = $child_id;
				}
			}

			foreach ($parent_map as $child_id => $parent_id) {
				if (!isset($child_map[$parent_id])) {
					$child_map[$parent_id] = array();
				}
				$child_map[$parent_id][] = $child_id;
			}

			$maps = array('parent_map' => $parent_map, 'child_map' => $child_map);
			$this->cache->save('categories.maps.' . $ent, $maps);
		}

		$this->category_data[$ent]['parent_map'] = $parent_map = $maps['parent_map'];
		$this->category_data[$ent]['child_map'] =  $child_map  = $maps['child_map'];

		// Getting hierarchy is easy because they already have parent/children,
		// hierarchy then is simply getting the root nodes from our collection
		$this->category_data[$ent]['hierarchy'] = array();

		foreach ($child_map[0] as $cat_id) {
			$this->category_data[$ent]['hierarchy'][] = $cats[$cat_id];
		}

		$h = new \Orb\Util\HierarchyStructure($cats);
		$h->parent_map = $this->category_data[$ent]['parent_map'];
		$h->child_map = $this->category_data[$ent]['child_map'];
		$this->category_data[$ent]['helper'] = $h;
	}
}
