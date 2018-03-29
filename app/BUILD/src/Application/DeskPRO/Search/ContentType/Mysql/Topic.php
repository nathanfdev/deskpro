<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search\ContentType\Mysql;

use Application\DeskPRO\Entity\Topic as TopicEntity;
use Application\DeskPRO\Search\Adapter\MysqlAdapter;
use Application\DeskPRO\Search\ContentType\AbstractContentType;
use Application\DeskPRO\Search\Indexer\Document;

class Topic extends AbstractContentType
{
    const ENTITY_NAME = 'DeskPRO:Topic';

    /**
     * @param TopicEntity $topic
     *
     * @return Document
     */
    public function objectToDocument($topic)
    {
        if ($topic->getStatus() != 'published') {
            $data                 = [];
            $data['id']           = $topic['id'];
            $data['content_type'] = 'news';
            $data['remove']       = true;

            $doc = Document::newFromArray($data);

            return $doc;
        }

        $data                 = [];
        $data['id']           = $topic['id'];
        $data['content_type'] = 'topic';
        $data['content']      = $topic['title']."\n".$topic['content']."\n";

        foreach ($topic->getLabelManager()->getLabelsArray() as $label) {
            $label = MysqlAdapter::encodeLabel($label);
            $data['content'] .= " $label ";
        }

        if ($topic->getGuide()) {
            $data['guide_id'] = $topic->getGuide()->getId();
        }

        $doc = Document::newFromArray($data);

        return $doc;
    }
}
