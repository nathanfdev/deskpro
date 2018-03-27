<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search\ContentType;

use Application\DeskPRO\App;
use Application\DeskPRO\Search\SearcherResult\ResultInterface;

abstract class AbstractContentType implements ContentTypeInterface
{
    const ENTITY_NAME = '';

    /**
     * Convert a result from a search into the real content object.
     *
     * @param \Application\DeskPRO\Search\SearcherResult\ResultInterface $result
     *
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
     *
     * @return array
     */
    public function resultsToObjects(array $results)
    {
        $ids = [];
        foreach ($results as $r) {
            $ids[] = $r->getId();
        }

        return App::getEntityRepository(static::ENTITY_NAME)->getByids($ids, true);
    }
}
