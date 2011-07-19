<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Search
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Search\Searcher\Elastic;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PersonContextInterface;

use Application\DeskPRO\Search\Adapter\ElasticAdapter;
use Application\DeskPRO\Search\Searcher\ContentSearcherInterface;

use Application\DeskPRO\Search\SearcherResult\Elastic\ResultSet;
use Application\DeskPRO\Search\SearcherResult\Elastic\Result;

/**
 * The content searcher searches: articles, downloads, ideas, news
 */
class ContentSearcher implements ContentSearcherInterface, PersonContextInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @var \Application\DeskPRO\Search\Adapter\ElasticAdapter
	 */
	protected $adapter;

	public function __construct(ElasticAdapter $adapter)
	{
		$this->adapter = $adapter;
	}

	/**
	 * @param \Application\DeskPRO\Entity\Person $person
	 */
	public function setPersonContext(Person $person)
	{
		$this->person = $person;
	}

	
	public function query($query_text)
	{
		$index = $this->adapter->getIndex('content');

		$query = new \Elastica_Query_QueryString($query_text);
		$query_out = new \Elastica_Query();
		$query_out->setQuery($query);

		$filter = $this->getPermissionFilter();
		if ($filter) {
			$query_out->setFilter($filter);
		}

		$e_result_set = $index->search($query_out);
		$result_set = ResultSet::newFromElasticResultSet($e_result_set);

		return $result_set;
	}

	public function labelled(array $labels)
	{
		$index = $this->adapter->getIndex('content');

		$query = new \Elastica_Query_Bool();
		foreach ($labels as $l) {
			$query->addMust(array('term' => array('labels' => $l)));
		}

		$query_out = \Elastica_Query::create($query);

		$filter = $this->getPermissionFilter();
		if ($filter) {
			$query_out->setFilter($filter);
		}

		$e_result_set = $index->search($query_out);
		$result_set = ResultSet::newFromElasticResultSet($e_result_set);

		return $result_set;
	}


	/**
	 * Find content similar to $content.
	 *
	 * @param string $content
	 * @param array $in_types Types you want to search in, or null for all
	 * @return \Application\DeskPRO\Search\SearcherResult\ResultSet
	 */
	public function similarContent($content, array $in_types = null)
	{
		$index = $this->adapter->getIndex('content');

		if ($in_types) {
			$query = new \Elastica_Query();
			$query->setParam('query', array(
				'fuzzy_like_this' => array(
					'like_text' => $content,
					'prefix_length' => 3,
				),
			));
		} else {
			$query = new \Elastica_Query();
			$query->setParam('query', array(
				'fuzzy_like_this' => array(
					'like_text' => $content,
					'prefix_length' => 3,
				)
			));
		}

		$filter = $this->getPermissionFilter();
		if ($filter) {
			$query->setFilter($filter);
		}

		$e_result_set = $index->search($query);
		$result_set = ResultSet::newFromElasticResultSet($e_result_set);

		return $result_set;
	}

	public function omnisearch($query_text)
	{
		$index = $this->adapter->getIndex('content');

		$query = new \Elastica_Query();
		$query->setParam('query', array(
			'fuzzy_like_this' => array(
				'like_text' => $query_text,
				'prefix_length' => 3,
			)
		));

		$e_result_set = $index->search($query);
		$result_set = ResultSet::newFromElasticResultSet($e_result_set);

		return $result_set;
	}


	/**
	 * Gets the filter that applies permissions to results
	 *
	 * @return \Elastica_Filter_Bool
	 */
	public function getPermissionFilter()
	{
		return null;
		if (!$this->person OR $this->person['is_agent']) {
			return null;
		}
		
		$filter = new \Elastica_Filter_Bool();

		$no_perm_types = array();

		#------------------------------
		# Articles
		#------------------------------

		if (!$this->person->getPermissionsManager()->ArticleCategories->hasRestrictions()) {
			$no_perm_types[] = 'article';
		} else {
			$term = new \Elastica_Filter_Bool();

			$cat_perms = $this->person->getPermissionsManager()->ArticleCategories->getSmallestSet();
			$type = $cat_perms['type'] == 'allowed' ? 'addMust' : 'addMustNot';

			$term->$type(array('term' => array('category_ids' => $cat_perms['ids'])));
			$filter->addShould($term);
		}

		#------------------------------
		# Downloads
		#------------------------------

		if (!$this->person->getPermissionsManager()->DownloadCategories->hasRestrictions()) {
			$no_perm_types[] = 'download';
		} else {
			$term = new \Elastica_Filter_Bool();

			$cat_perms = $this->person->getPermissionsManager()->DownloadCategories->getSmallestSet();
			$type = $cat_perms['type'] == 'allowed' ? 'addMust' : 'addMustNot';

			$term->$type(array('term' => array('category_id' => $cat_perms['ids'])));
			$filter->addShould($term);
		}

		#------------------------------
		# Ideas
		#------------------------------

		if (!$this->person->getPermissionsManager()->IdeaCategories->hasRestrictions()) {
			$no_perm_types[] = 'idea';
		} else {
			$term = new \Elastica_Filter_Bool();

			$cat_perms = $this->person->getPermissionsManager()->IdeaCategories->getSmallestSet();
			$type = $cat_perms['type'] == 'allowed' ? 'addMust' : 'addMustNot';

			$term->$type(array('term' => array('category_id' => $cat_perms['ids'])));
			$filter->addShould($term);
		}

		#------------------------------
		# News
		#------------------------------

		if (!$this->person->getPermissionsManager()->NewsCategories->hasRestrictions()) {
			$no_perm_types[] = 'idea';
		} else {
			$term = new \Elastica_Filter_Bool();

			$cat_perms = $this->person->getPermissionsManager()->NewsCategories->getSmallestSet();
			$type = $cat_perms['type'] == 'allowed' ? 'addMust' : 'addMustNot';

			$term->$type(array('term' => array('category_id' => $cat_perms['ids'])));
			$filter->addShould($term);
		}

		#------------------------------
		# No perm types
		#------------------------------

		// If all four have no perms, then we dont need this filter at all
		if (count($no_perm_types) == 4) {
			return null;
		}

		// Otherwise we need another term that just says the content type
		// is one of the ones there are no permissions for
		if ($no_perm_types) {
			$term = new \Elastica_Filter_Terms();
			$term->addShould(array('type' => $no_perm_types));
			$filter->addShould($term);
		}

		return $filter;
	}
}