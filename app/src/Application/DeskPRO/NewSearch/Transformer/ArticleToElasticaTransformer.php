<?php

namespace Application\DeskPRO\NewSearch\Transformer;

use Application\DeskPRO\App;
use Elastica\Document;
use Application\DeskPRO\Entity\Article;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Orb\Util\Arrays;

class ArticleToElasticaTransformer implements ModelToElasticaTransformerInterface
{
	/**
	 * Transform
	 *
	 * @param Article $object
	 * @param array $fields
	 *
	 * @return Document
	 */
	public function transform($object, array $fields)
	{
		$document = new Document();
		$document->setId($object->id);

		$document->set('title', $object->getRealTitle());
		$document->set('content', $object->getContentPlain());
		$document->set('status', $object->status);

		$cat_ids = array();
		foreach ($object->categories as $c) {
			$cat_ids[] = $c->id;
		}
		if ($cat_ids) {
			$document->set('category_ids', $cat_ids);
		}

		if ($object->labels) {
			$labels = Arrays::map(function ($l) { return $l->label; }, $object->labels);
			$document->set('labels', $labels);
		}

		$sticky_words = App::$container->getDb()->fetchAllCol("
			SELECT word
			FROM search_sticky_result
			WHERE object_type = ? AND object_id = ?
		", array('DeskPRO:Article', $object->id));
		if ($sticky_words) {
			$document->set('sticky_words', $sticky_words);
		}

		$document->set('date_created', $object->date_created->format('Y-m-d H:i:s'));
		$document->set('date_active', date('Y-m-d H:i:s'));

		return $document;
	}
} 