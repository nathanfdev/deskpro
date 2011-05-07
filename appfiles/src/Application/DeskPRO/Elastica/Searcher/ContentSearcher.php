<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Elastica
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Elastica\Searcher;

class ContentSearcher extends AbstractSearcher
{
	public function search($query)
	{
		$index = $this->manager->getIndex('content');

		$query = new \Elastica_Query_QueryString($query);
		$query_out = new \Elastica_Query();
		$query_out->setQuery($query);

		$filter = $this->getPermissionFilter();
		if ($filter) {
			$query_out->setFilter($filter);
		}

		$documents = $index->search($query)->getResults();
		$results = $this->documentsToResults($documents);

		return $results;
	}

	public function labelled($labels)
	{
		// Explode into an array of labels if not already
		// given an array
		if (!is_array($labels)) {
			$labels = explode(',', $labels);
			array_walk($labels, 'trim');
		}

		$index = $this->manager->getIndex('content');

		$query = new \Elastica_Query_Bool();
		foreach ($labels as $l) {
			$query->addMust(array('term' => array('labels' => $l)));
		}

		$query_out = \Elastica_Query::create($query);

		$filter = $this->getPermissionFilter();
		if ($filter) {
			$query_out->setFilter($filter);
		}

		$search_result = $index->search($query_out);

		$documents = $search_result->getResults();
		$results = $this->documentsToResults($documents);

		return $results;
	}


	/**
	 * Gets the terms that apply permissions
	 * 
	 * @return \Elastica_Query_Bool
	 */
	public function getPermissionFilter()
	{
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

	public function similarArticleToTicket($ticket)
	{
		$index = $this->manager->getIndex('content');

		$text = $ticket['subject'] . "\n" . $ticket->getFirstMessage()->getMessageText();

		$query = new \Application\DeskPRO\Elastica\Query\MoreLikeThis($text);
		$query_out = new \Elastica_Query();
		$query_out->setQuery($query);

		$filter = $this->getPermissionFilter();
		if ($filter) {
			$query_out->setFilter($filter);
		}

		$documents = $index->search($query)->getResults();
		$results = $this->documentsToResults($documents);

		return $results;
	}
}