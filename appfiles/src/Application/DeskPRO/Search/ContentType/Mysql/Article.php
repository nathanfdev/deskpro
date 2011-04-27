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

namespace Application\DeskPRO\Search\ContentType;

use \Application\DeskPRO\Search\Adapter\MysqlAdapter;
use \Application\DeskPRO\Search\SearcherResult\ResultInterface;
use \Application\DeskPRO\Search\Indexer\Document;

class Article extends AbstractContentType
{
	const ENTITY_NAME = 'DeskPRO:Article';
	
	public function objectToDocument($article)
	{
		$data = array();
		$data['id'] = $article['id'];
		$data['content_type'] = 'article';
		$data['content'] = $article['title'] . "\n" . $article['content'] . "\n";

		foreach ($article->getLabelManager()->getLabelsArray() as $label) {
			$label = MysqlAdapter::encodeLabel($label);
			$data['content'] .= " $label ";
		}

		$doc = Document::newFromArray($data);

		return $doc;
	}
}