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
use \Application\DeskPRO\ORM\EntityRepository\NestedTreeRepository;

use Doctrine\ORM\Query,
    Gedmo\Tree\Strategy,
    Gedmo\Tree\Strategy\ORM\Nested,
    Gedmo\Exception\InvalidArgumentException,
    Doctrine\ORM\Proxy\Proxy;

use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity\Idea as NewsEntity;
use Application\DeskPRO\Searcher\NewsSearch;

use \Orb\Util\Arrays;
use \Orb\Util\Strings;

class NewsCategory extends AbstractNestedTreeCategoryRepository
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
			SELECT id, title
			FROM news_categories
			ORDER BY id DESC
		", array(), 'id');

		return $this->all_cats;
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
			FROM DeskPRO:NewsCategory c INDEX BY c.id
			ORDER BY c.id DESC
		")->execute();
	}

	public function getAllCounts(PersonEntity $person_context = null, $cache_name = 'portal')
	{
		$cache = App::getCache($cache_name);
		$cache_id = "counts_news";

		if (($counts = $cache->load($cache_id)) === false) {
			$counts = array(0 => 0);
			foreach ($this->children() as $c) {
				$searcher = new NewsSearch();
				$searcher->setPersonContext($person_context);
				$searcher->addTerm(NewsSearch::TERM_CATEGORY, 'is', $c['id']);
				$searcher->addTerm(NewsSearch::TERM_PUBLISHED, 'is', 1);

				$counts[$c['id']] = $searcher->getCount();

				// 0 is sum of all root nodes
				if (!$c['depth']) {
					$counts[0] += $counts[$c['id']];
				}
			}

			$cache->save($counts, $cache_id);
		}

		return $counts;
	}
}