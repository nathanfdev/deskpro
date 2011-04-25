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
		$search_result = $index->search($query_out);

		$documents = $search_result->getResults();
		$results = $this->documentsToResults($documents);

		return $results;
	}
}