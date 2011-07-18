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

namespace Application\DeskPRO\Search\ContentType\Mysql;

use Application\DeskPRO\Search\ContentType\AbstractContentType;
use Application\DeskPRO\Search\Adapter\MysqlAdapter;
use Application\DeskPRO\Search\SearcherResult\ResultInterface;
use Application\DeskPRO\Search\Indexer\Document;

class News extends AbstractContentType
{
	const ENTITY_NAME = 'DeskPRO:News';
	
	public function objectToDocument($news)
	{
		$data = array();
		$data['id'] = $news['id'];
		$data['content_type'] = 'news';
		$data['title'] = $news['title'];
		$data['content'] = $news['content'];
		$data['category_id'] = $news->category['id'];
		$data['labels'] = array();

		foreach ($news->getLabelManager()->getLabelsArray() as $label) {
			$data['labels'][] = $label;
		}

		$doc = Document::newFromArray($data);

		return $doc;
	}
}