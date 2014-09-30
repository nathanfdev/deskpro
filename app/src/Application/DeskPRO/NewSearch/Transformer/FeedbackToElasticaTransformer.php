<?php

namespace Application\DeskPRO\NewSearch\Transformer;

use Application\DeskPRO\App;
use Elastica\Document;
use Application\DeskPRO\Entity\Feedback;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Orb\Util\Arrays;

class FeedbackToElasticaTransformer implements ModelToElasticaTransformerInterface
{
	/**
	 * Transform
	 *
	 * @param Feedback $object
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

		if ($object->category) {
			$document->set('category_id', $object->category->id);
		}

		if ($object->labels) {
			$labels = Arrays::map(function ($l) { return $l->label; }, $object->labels);
			$document->set('labels', $labels);
		}

		$sticky_words = App::$container->getDb()->fetchAllCol("
			SELECT word
			FROM search_sticky_result
			WHERE object_type = ? AND object_id = ?
		", array('DeskPRO:Feedback', $object->id));
		if ($sticky_words) {
			$document->set('sticky_words', $sticky_words);
		}

		return $document;
	}
} 