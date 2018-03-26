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

class News extends AbstractContentType
{
    const ENTITY_NAME = 'DeskPRO:News';

    public function objectToDocument($news)
    {
        if ($news->status != 'published') {
            $data                 = [];
            $data['id']           = $news['id'];
            $data['content_type'] = 'news';
            $data['remove']       = true;

            $doc = Document::newFromArray($data);

            return $doc;
        }

        $data                 = [];
        $data['id']           = $news['id'];
        $data['content_type'] = 'news';
        $data['content']      = $news['title']."\n".$news['content']."\n";

        foreach ($news->getLabelManager()->getLabelsArray() as $label) {
            $label = MysqlAdapter::encodeLabel($label);
            $data['content'] .= " $label ";
        }

        if ($news->category) {
            $data['category_id'] = $news->category->id;
        }

        $doc = Document::newFromArray($data);

        return $doc;
    }
}
