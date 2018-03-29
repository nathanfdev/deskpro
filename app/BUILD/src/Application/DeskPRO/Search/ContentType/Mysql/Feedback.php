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

class Feedback extends AbstractContentType
{
    const ENTITY_NAME = 'DeskPRO:Feedback';

    public function objectToDocument($feedback)
    {
        $data                 = [];
        $data['id']           = $feedback['id'];
        $data['content_type'] = 'feedback';
        $data['content']      = $feedback['title']."\n".$feedback['content']."\n";

        foreach ($feedback->getLabelManager()->getLabelsArray() as $label) {
            $label = MysqlAdapter::encodeLabel($label);
            $data['content'] .= " $label ";
        }

        if ($feedback->category) {
            $data['category_id'] = $feedback->category->id;
        }

        $doc = Document::newFromArray($data);

        return $doc;
    }
}
