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

class Download extends AbstractContentType
{
    const ENTITY_NAME = 'DeskPRO:Download';

    public function objectToDocument($download)
    {
        if ($download->status != 'published') {
            $data                 = [];
            $data['id']           = $download['id'];
            $data['content_type'] = 'download';
            $data['remove']       = true;

            $doc = Document::newFromArray($data);

            return $doc;
        }

        $data                 = [];
        $data['id']           = $download['id'];
        $data['content_type'] = 'download';
        $data['content']      = $download['title']."\n".$download['content']."\n";

        foreach ($download->getLabelManager()->getLabelsArray() as $label) {
            $label = MysqlAdapter::encodeLabel($label);
            $data['content'] .= " $label ";
        }

        if ($download->category) {
            $data['category_id'] = $download->category->id;
        }

        $doc = Document::newFromArray($data);

        return $doc;
    }
}
