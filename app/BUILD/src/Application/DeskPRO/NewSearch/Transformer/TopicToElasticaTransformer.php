<?php

namespace Application\DeskPRO\NewSearch\Transformer;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Topic;
use Elastica\Document;

/**
 * Class TopicToElasticaTransformer.
 */
class TopicToElasticaTransformer extends AbstractToElasticaTransformer
{
    /**
     * {@inheritdoc}
     *
     * @param Topic $object
     */
    public function transform($object, array $fields)
    {
        $document = new Document();
        $document->setId($object->getId());

        $document->set('title', $object->getRealTitle());
        $document->set('content', $object->getContentPlain());
        $document->set('status', $object->getStatus());

        if ($object->getGuide()) {
            $document->set('guide_id', $object->getGuide()->getId());
        }

        $document->set('date_created', $object->getDateCreated()->format('Y-m-d H:i:s'));
        $document->set('date_active', date('Y-m-d H:i:s'));

        $stickyWords = App::$container->getDb()->fetchAllCol('
            SELECT word
            FROM search_sticky_result
            WHERE object_type = ? AND object_id = ?
        ', ['DeskPRO:Download', $object->getId()]);
        if ($stickyWords) {
            $document->set('sticky_words', $stickyWords);
        }

        return $document;
    }
}
