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

class ContentSearch extends AbstractSearcher
{
	public function search($query)
	{
		$index = $this->manager->getIndex('content');

		$documents = $index->search($query);
		$results = $this->documentsToResults($documents);

		return $results;
	}
}