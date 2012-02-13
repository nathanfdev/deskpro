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

class Download extends AbstractContentType
{
	const ENTITY_NAME = 'DeskPRO:Download';
	
	public function objectToDocument($download)
	{
		$data = array();
		$data['id'] = $download['id'];
		$data['content_type'] = 'download';
		$data['content'] = $download['title'] . "\n" . $download['content'] . "\n";

		foreach ($download->getLabelManager()->getLabelsArray() as $label) {
			$label = MysqlAdapter::encodeLabel($label);
			$data['content'] .= " $label ";
		}

		$doc = Document::newFromArray($data);

		return $doc;
	}
}