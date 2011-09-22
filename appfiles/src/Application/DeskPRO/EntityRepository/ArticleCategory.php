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
use Application\DeskPRO\ORM\EntityRepository\NestedTreeRepository;
use Application\DeskPRO\EntityRepository\Helper\CategoryHierarchy;
use Application\DeskPRO\EntityRepository\Helper\CommentHelper;

use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Searcher\ArticleSearch;

use Orb\Util\Arrays;
use Orb\Util\Strings;

class ArticleCategory extends AbstractCategoryRepository
{
	/**
	 * @var \Application\DeskPRO\EntityRepository\Helper\CommentHelper
	 */
	protected $_comment_helper = null;

	/**
	 * @return \Application\DeskPRO\EntityRepository\Helper\CommentHelper
	 */
	public function getCommentHelper()
	{
		if ($this->_comment_helper !== null) {
			return $this->_comment_helper;
		}

		$this->_comment_helper = new CommentHelper(
			$this->getEntityManager(),
			$this,
			$this->getEntityName(),
			$this->getClassMetadata(),
			'DeskPRO:ArticleComment',
			'article_comments',
			'article_id'
		);

		return $this->_comment_helper;
	}

	public function getCategoriesById(array $ids)
	{
		$ids = Arrays::removeFalsey($ids);

		if (!$ids) return array();

		$ids = implode(',', $ids);

		return $this->getEntityManager()->createQuery("
			SELECT c
			FROM DeskPRO:ArticleCategory c
			WHERE c.id IN ($ids)
			ORDER BY c.display_order
		")->execute();
	}

	public function getCategoryOptions()
	{
		if ($this->all_cats !== null) return $this->all_cats;

		$this->all_cats = App::getDb()->fetchAllKeyed("
			SELECT id, parent_id, title
			FROM article_categories
			ORDER BY display_order DESC
		", array(), 'id');

		return $this->all_cats;
	}

	public function getFullHierarchy()
	{
		if ($this->hierarchy !== null) return $this->hierarchy;

		$this->hierarchy = Arrays::intoHierarchy($this->getCategoryOptions());

		return $this->hierarchy;
	}


	public function getBySlug($slug)
	{
		$id = Strings::extractRegexMatch('#^([0-9]+)#', $slug, 1);
		if (!$id) return null;

		return $this->find($id);
	}

	public function getAllCounts(PersonEntity $person_context = null, $cache_name = 'portal', $from_parent = 0)
	{
		// TODO
		// move caching mechanism into own class, like stuff is done with
		// Application\DeskPRO\Publish\AgentHelper

		$cache = App::getCache($cache_name);
		$cache_id = "counts_articles";

		if (($counts = $cache->load($cache_id)) === false) {
			$counts = array('0' => 0, '0_total' => 0);

			foreach ($this->children() as $c) {
				$searcher = new ArticleSearch();
				$searcher->setPersonContext($person_context);
				$searcher->addTerm(ArticleSearch::TERM_CATEGORY_SPECIFIC, 'is', $c['id']);
				$searcher->addTerm(ArticleSearch::TERM_STATUS, 'is', 'published');

				$counts[$c['id']] = $searcher->getCount();

				$counts['0_total'] += $counts[$c['id']];
			}

			foreach ($this->getCategoryIds() as $c_id) {
				$total = 0;
				if (isset($counts[$c_id])) {
					$total = $counts[$c_id];
				}

				foreach ($this->getChildrenIds($c_id, false) as $child_id) {
					if (isset($counts[$child_id])) {
						$total += $counts[$child_id];
					}
				}

				$counts["{$c_id}_total"] = $total;
			}

			$cache->save($counts, $cache_id, array('article_structure'));
		}

		return $counts;
	}
}
