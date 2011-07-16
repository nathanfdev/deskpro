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
use Application\DeskPRO\Searcher\DownloadSearch;

use Orb\Util\Arrays;
use Orb\Util\Strings;

class DownloadCategory extends AbstractNestedTreeCategoryRepository
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
			FROM download_categories
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

	public function getAllCounts(PersonEntity $person_context = null, $cache_name = 'portal')
	{
		$cache = App::getCache($cache_name);
		$cache_id = "counts_downloads";

		if (($counts = $cache->load($cache_id)) === false) {
			$counts = array(0 => 0);
			foreach ($this->children() as $c) {
				$searcher = new DownloadSearch();
				$searcher->setPersonContext($person_context);
				$searcher->addTerm(DownloadSearch::TERM_CATEGORY, 'is', $c['id']);

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