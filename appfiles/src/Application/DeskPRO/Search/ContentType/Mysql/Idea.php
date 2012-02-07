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

class Idea extends AbstractContentType
{
	const ENTITY_NAME = 'DeskPRO:Idea';
	
	public function objectToDocument($feedback)
	{
		$data = array();
		$data['id'] = $feedback['id'];
		$data['content_type'] = 'feedback';
		$data['content'] = $feedback['title'] . "\n" . $feedback['content'] . "\n";

		foreach ($feedback->getLabelManager()->getLabelsArray() as $label) {
			$label = MysqlAdapter::encodeLabel($label);
			$data['content'] .= " $label ";
		}

		$doc = Document::newFromArray($data);

		return $doc;
	}
}