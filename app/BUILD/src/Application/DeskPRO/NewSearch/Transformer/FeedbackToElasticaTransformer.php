<?php

namespace Application\DeskPRO\NewSearch\Transformer;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\CommunityTopic;
use Elastica\Document;

/**
 * Class FeedbackToElasticaTransformer.
 */
class FeedbackToElasticaTransformer extends AbstractToElasticaTransformer
{
    /**
     * {@inheritdoc}
     *
     * @param CommunityTopic $object
     */
    public function transform($object, array $fields)
    {
        $document = new Document();
        $document->setId($object->getId());

        $document->set('title', $object->getRealTitle());
        $document->set('content', $object->getContentPlain());
        $document->set('status', $object->getStatus());

        if ($object->getCategory()) {
            $document->set('category_id', $object->getCategory()->getId());
        }

        $sticky_words = App::$container->getDb()->fetchAllCol('
            SELECT word
            FROM search_sticky_result
            WHERE object_type = ? AND object_id = ?
        ', ['DeskPRO:CommunityTopic', $object->getId()]);
        if ($sticky_words) {
            $document->set('sticky_words', $sticky_words);
        }

        $document->set('date_created', $object->getDateCreated()->format('Y-m-d H:i:s'));
        $document->set('date_active', date('Y-m-d H:i:s'));

        $this->transformCustomData($object, $document);
        $this->transformLabels($object, $document);

        return $document;
    }
}
