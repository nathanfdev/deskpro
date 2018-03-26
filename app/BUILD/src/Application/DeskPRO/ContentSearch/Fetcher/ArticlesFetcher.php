<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ContentSearch\Fetcher;

use Application\DeskPRO\App;

class ArticlesFetcher extends AbstractFetcher
{
    const TYPENAME = 'articles';

    /**
     * Returns an array of entities identified by $related_ids, that the user is able to see.
     *
     * @param array $related_ids
     * @param bool  $all
     *
     * @return array
     */
    public function getEntities(array $related_ids, $all = false)
    {
        if ($all) {
            return App::getEntityRepository('DeskPRO:Article')->getByIds($related_ids);
        } else {
            return App::getEntityRepository('DeskPRO:Article')->getByIdsWithContext($related_ids, $this->person);
        }
    }
}
