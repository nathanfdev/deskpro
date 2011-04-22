<?php

namespace Application\DeskPRO\Elastica\Provider;

use FOQ\ElasticaBundle\Provider\ProviderInterface;
use Closure;

use Application\DeskPRO\App;
use Application\DeskPRO\Elastica\Transformer\ObjectTransformer;
use Orb\Util\Arrays;

class ArticleProvider implements ProviderInterface
{
	/**
	 * @var \Elastica_Type
	 */
	protected $type;

	public function __construct(\Elastica_Type $type)
	{
		$this->type = $type;
	}

	public function populate(Closure $loggerClosure)
	{
		$article_ids = App::getDb()->fetchAllCol("
			SELECT id FROM articles
		");

		$batches = array_chunk($batches, 20, false);

		$transformer = new ObjectTransformer();

		foreach ($batches as $batch) {
			$articles = App::getOrm()->createQuery("
				SELECT a
				FROM DeskPRO:Article
				WHERE a.id IN (" . implode(',', $batch) . ")
			");

			$documents = array();

			foreach ($articles as $article) {
				$data = $transformer->transform($article, array());
				$documents[] = new Elastica_Document($object->$identifierGetter(), $data);
			}

			$this->type->addDocuments($documents);

			App::getOrm()->clear();
		}
	}
}
