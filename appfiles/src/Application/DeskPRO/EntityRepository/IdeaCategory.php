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

use Doctrine\ORM\Query,
    Gedmo\Tree\Strategy,
    Gedmo\Tree\Strategy\ORM\Nested,
    Gedmo\Exception\InvalidArgumentException,
    Doctrine\ORM\Proxy\Proxy;

use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity\Idea as IdeaEntity;
use Application\DeskPRO\Searcher\IdeaSearch;

use \Orb\Util\Arrays;
use \Orb\Util\Strings;

class IdeaCategory extends AbstractCategoryRepository
{
	protected $all_cats = null;

	/**
	 * Get an array of categories
	 *
	 * @return array
	 */
	public function getCategoryOptions()
	{
		if (!$this->all_cats === null) return $this->all_cats;

		$this->all_cats = App::getDb()->fetchAllKeyed("
			SELECT id, parent_id title
			FROM idea_categories
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

	public function getAll()
	{
		return $this->getEntityManager()->createQuery("
			SELECT c
			FROM DeskPRO:IdeaCategory c INDEX BY c.id
			ORDER BY c.id DESC
		")->execute();;
	}

	public function getAllCounts(PersonEntity $person_context = null, $cache_name = 'portal')
	{
		$cache = App::getCache($cache_name);
		$cache_id = "counts_ideas";

		if (($counts = $cache->load($cache_id)) === false) {
			$counts = array(0 => array('popular' => 0, 'new' => 0, 'active' => 0, 'closed' => 0));
			foreach ($this->children() as $c) {

				$cat_counts = array();

				$searcher = new IdeaSearch();
				$searcher->setPersonContext($person_context);
				$searcher->addTerm(IdeaSearch::TERM_CATEGORY, 'is', $c['id']);
				$searcher->addTerm(IdeaSearch::TERM_STATUS, 'is', IdeaEntity::STATUS_NEW);
				$cat_counts['new'] = $searcher->getCount();

				$searcher = new IdeaSearch();
				$searcher->setPersonContext($person_context);
				$searcher->addTerm(IdeaSearch::TERM_CATEGORY, 'is', $c['id']);
				$searcher->addTerm(IdeaSearch::TERM_STATUS, 'is', IdeaEntity::STATUS_ACTIVE);
				$cat_counts['active'] = $searcher->getCount();

				$searcher = new IdeaSearch();
				$searcher->setPersonContext($person_context);
				$searcher->addTerm(IdeaSearch::TERM_CATEGORY, 'is', $c['id']);
				$searcher->addTerm(IdeaSearch::TERM_STATUS, 'is', IdeaEntity::STATUS_CLOSED);
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

			$cache->save($counts, $cache_id);
		}

		return $counts;
	}
}
