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
		$index = $this->manager->getIndex('ticket');

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

		$index = $this->manager->getIndex('ticket');

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

		$allowed_ids = $this->person->getAllowedDepartments();
		$disallowed_ids = $this->person->getDisallowedDepartments();

		// Allowed everything
		if (!$disallowed_ids) {
			return null;
		}

		$term = new \Elastica_Filter_Bool();

		if (count($allowed_ids) > count($disallowed_ids)) {
			$term->addShould(array('term' => array('department_id' => $allowed_ids)));
		} else {
			$term->addMustNot(array('term' => array('department_id' => $disallowed_ids)));
		}

		$filter->addShould($term);

		return $filter;
	}
}