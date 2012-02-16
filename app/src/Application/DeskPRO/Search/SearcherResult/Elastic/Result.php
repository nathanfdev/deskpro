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

use Application\DeskPRO\Search\SearcherResult\Result as BaseResult;

class Result extends BaseResult
{
	public static function newFromElasticResult(\Elastica_Result $e_result)
	{
		$result = self::newFromArray(array(
			'id'           => $e_result->getId(),
			'content_type' => $e_result->getType(),
			'highlights'   => $e_result->getHighlights(),
			'score'        => $e_result->getScore()
		));

		return $result;
	}
}
