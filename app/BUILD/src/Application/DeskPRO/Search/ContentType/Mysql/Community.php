<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search\ContentType\Mysql;

use Application\DeskPRO\Search\Adapter\MysqlAdapter;
use Application\DeskPRO\Search\ContentType\AbstractContentType;
use Application\DeskPRO\Search\Indexer\Document;

class Community extends AbstractContentType
{
    const ENTITY_NAME = 'DeskPRO:CommunityTopic';

    public function objectToDocument($communityTopic)
    {
        $data                 = [];
        $data['id']           = $communityTopic['id'];
        $data['content_type'] = 'community';
        $data['content']      = $communityTopic['title']."\n".$communityTopic['content']."\n";

        foreach ($communityTopic->getLabelManager()->getLabelsArray() as $label) {
            $label = MysqlAdapter::encodeLabel($label);
            $data['content'] .= " $label ";
        }

        if ($communityTopic->category) {
            $data['forum_id'] = $communityTopic->category->id;
        }

        $doc = Document::newFromArray($data);

        return $doc;
    }
}
