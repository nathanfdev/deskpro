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
use Application\DeskPRO\Search\Adapter\MysqlAdapter;
use Application\DeskPRO\Search\SearcherResult\ResultInterface;
use Application\DeskPRO\Search\Indexer\Document;

class Feedback extends AbstractContentType
{
	const ENTITY_NAME = 'DeskPRO:Feedback';
	
	public function objectToDocument($feedback)
	{
		$data = array();
		$data['id'] = $feedback['id'];
		$data['content_type'] = 'feedback';
		$data['title'] = $feedback['title'];
		$data['content'] = $feedback['content'];
		$data['category_id'] = $feedback->category['id'];
		$data['labels'] = array();

		foreach ($feedback->getLabelManager()->getLabelsArray() as $label) {
			$data['labels'][] = $label;
		}

		$doc = Document::newFromArray($data);

		return $doc;
	}
}