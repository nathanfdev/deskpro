<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity;

class SearchStickyResult extends AbstractEntityRepository
{
    public function getWordsForObject($object)
    {
        if ($object instanceof Entity\Article) {
            $objectType = 'DeskPRO:Article';
        } elseif ($object instanceof Entity\Download) {
            $objectType = 'DeskPRO:Download';
        } elseif ($object instanceof Entity\News) {
            $objectType = 'DeskPRO:News';
        } elseif ($object instanceof Entity\Feedback) {
            $objectType = 'DeskPRO:Feedback';
        } elseif ($object instanceof Entity\Topic) {
            $objectType = 'DeskPRO:Topic';
        } else {
            throw new \InvalidArgumentException('Unknown type');
        }

        return $this->getWordsFor($objectType, $object->getId());
    }

    public function getWordsFor($object_type, $object_id)
    {
        return $this->getEntityManager()->getConnection()->fetchAllCol('
            SELECT word
            FROM search_sticky_result
            WHERE object_type = ? AND object_id = ?
        ', [$object_type, $object_id]);
    }
}
