<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ContentSearch\Fetcher;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\CommunityTopic;

class FeedbackFetcher extends AbstractFetcher
{
    const TYPENAME = 'feedback';

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
            return App::getEntityRepository(CommunityTopic::class)->getByIds($related_ids);
        } else {
            return App::getEntityRepository(CommunityTopic::class)->getByIdsWithContext($related_ids, $this->person);
        }
    }
}
