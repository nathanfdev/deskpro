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
	
	public function objectToDocument($idea)
	{
		$data = array();
		$data['id'] = $idea['id'];
		$data['content_type'] = 'idea';
		$data['content'] = $idea['title'] . "\n" . $idea['content'] . "\n";

		foreach ($idea->getLabelManager()->getLabelsArray() as $label) {
			$label = MysqlAdapter::encodeLabel($label);
			$data['content'] .= " $label ";
		}

		$doc = Document::newFromArray($data);

		return $doc;
	}
}