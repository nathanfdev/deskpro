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

namespace Application\DeskPRO\Search\SearcherResult\Elastic;

use Application\DeskPRO\Search\SearcherResult\ResultSet as BaseResultSet;

class ResultSet extends BaseResultSet
{
	public static function newFromElasticResultSet(\Elastica_ResultSet $e_result_set)
	{
		$total = $e_result_set->getTotalHits();
		$results = array();

		foreach ($e_result_set->getResults() as $e_result) {
			$results[] = Result::newFromElasticResult($e_result);
		}

		return new self($total, $results);
	}
}