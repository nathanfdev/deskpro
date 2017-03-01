<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\ManualTopic;
use Application\DeskPRO\Entity\News;

class SearchStickyResult extends AbstractEntityRepository
{
    public function getWordsForObject($object)
    {
        if ($object instanceof Article) {
            $objectType = 'DeskPRO:Article';
        } elseif ($object instanceof Download) {
            $objectType = 'DeskPRO:Download';
        } elseif ($object instanceof News) {
            $objectType = 'DeskPRO:News';
        } elseif ($object instanceof Feedback) {
            $objectType = 'DeskPRO:Feedback';
        } elseif ($object instanceof ManualTopic) {
            $objectType = 'DeskPRO:ManualTopic';
        } else {
            throw new \InvalidArgumentException('Unknown type');
        }

        return $this->getWordsFor($objectType, $object->id);
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
