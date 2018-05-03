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

class Article extends AbstractContentType
{
    const ENTITY_NAME = 'DeskPRO:Article';

    public function objectToDocument($article)
    {
        if ($article->status != 'published') {
            $data                 = [];
            $data['id']           = $article['id'];
            $data['content_type'] = 'article';
            $data['remove']       = true;

            $doc = Document::newFromArray($data);

            return $doc;
        }

        $data                 = [];
        $data['id']           = $article['id'];
        $data['content_type'] = 'article';
        $data['content']      = $article['title']."\n".$article['content']."\n";

        foreach ($article->getLabelManager()->getLabelsArray() as $label) {
            $label = MysqlAdapter::encodeLabel($label);
            $data['content'] .= " $label ";
        }

        $x = 0;
        foreach ($article->categories as $c) {
            $k = 'category_id';
            if ($x++) {
                $k .= '_'.$x;
            }

            $data[$k] = $c->id;
        }

        $doc = Document::newFromArray($data);

        return $doc;
    }
}
