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

namespace Application\DeskPRO\NewSearch\Transformer;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Article;
use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Orb\Util\Arrays;

/**
 * Class ArticleToElasticaTransformer.
 */
class ArticleToElasticaTransformer implements ModelToElasticaTransformerInterface
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

        $cat_ids = [];
        foreach ($object->getCategories() as $c) {
            $cat_ids[] = $c->getId();
        }
        if ($cat_ids) {
            $document->set('category_ids', $cat_ids);
        }

        if ($object->getLabels()) {
            $labels = Arrays::map(function ($l) {
                return $l->label;
            }, $object->getLabels());
            $document->set('labels', $labels);
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

        return $document;
    }
}
