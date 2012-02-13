<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Search
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Search\ContentType\Elastic;

use Application\DeskPRO\Search\ContentType\AbstractContentType;
use Application\DeskPRO\Search\SearcherResult\ResultInterface;
use Application\DeskPRO\Search\Indexer\Document;

class Article extends AbstractContentType
{
	const ENTITY_NAME = 'DeskPRO:Article';
	
	public function objectToDocument($article)
	{
		$data = array();
		$data['id'] = $article['id'];
		$data['content_type'] = 'article';
		$data['title'] = $article['title'];
		$data['content'] = $article['content'];
		$data['labels'] = array();

		$data['category_ids'] = array();
		foreach ($article->categories as $c) {
			$data['category_ids'][] = $c['id'];
		}

		foreach ($article->getLabelManager()->getLabelsArray() as $label) {
			$data['labels'][] = $label;
		}

		$doc = Document::newFromArray($data);

		return $doc;
	}
}