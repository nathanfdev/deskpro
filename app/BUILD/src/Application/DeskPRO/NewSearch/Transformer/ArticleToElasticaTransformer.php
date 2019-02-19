<?php

namespace Application\DeskPRO\NewSearch\Transformer;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Article;
use Elastica\Document;

/**
 * Class ArticleToElasticaTransformer.
 */
class ArticleToElasticaTransformer extends AbstractToElasticaTransformer
{
    /**
     * {@inheritdoc}
     *
     * @param Article $object
     */
    public function transform($object, array $fields)
    {
        $document = new Document();
        $document->setId($object->getId());

        $titles  = [$object->getRealTitle()];
        $content = [$object->getContentPlain()];

        foreach ($object->getTitleTranslations() as $translation) {
            $titles[] = $translation->getValue();
        }
        foreach ($object->getContentTranslations() as $translation) {
            $content[] = $translation->getValue();
        }

        $document->set('title', implode(' ', $titles));
        $document->set('content', implode(' ', $content));
        $document->set('status', $object->getStatus());
        $document->set('hidden_status', $object->getHiddenStatus());

        $cat_ids = [];
        foreach ($object->getCategories() as $c) {
            $cat_ids[] = $c->getId();
        }
        if ($cat_ids) {
            $document->set('category_ids', $cat_ids);
        }

        $sticky_words = App::$container->getDb()->fetchAllCol('
            SELECT word
            FROM search_sticky_result
            WHERE object_type = ? AND object_id = ?
        ', ['DeskPRO:Article', $object->getId()]);
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
