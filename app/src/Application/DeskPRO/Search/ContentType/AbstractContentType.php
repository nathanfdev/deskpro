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

namespace Application\DeskPRO\Search\ContentType;

use Application\DeskPRO\Search\SearcherResult\ResultInterface;

use Application\DeskPRO\App;

abstract class AbstractContentType implements ContentTypeInterface
{
	const ENTITY_NAME = '';

	/**
	 * Convert a result from a search into the real content object.
	 *
	 * @param \Application\DeskPRO\Search\SearcherResult\ResultInterface $result
	 * @return mixed
	 */
	public function resultToObject(ResultInterface $result)
	{
		return App::getEntityRepository(static::ENTITY_NAME)->find($result->getId());
	}

	/**
	 * Converts many results of this type into real objects.
	 *
	 * Default (inefficient) implementation.
	 *
	 * @param \Application\DeskPRO\Search\SearcherResult\ResultInterface[] $results
	 * @return array
	 */
	public function resultsToObjects(array $results)
	{
		$objects = array();

		$ids = array();
		foreach ($results as $r) {
			$ids[] = $r->getId();
		}

		$objects = App::getEntityRepository(static::ENTITY_NAME)->getByids($ids, true);

		return $objects;
	}
}
