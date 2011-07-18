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
		$data['title'] = $download['title'];
		$data['content'] = $download['content'];
		$data['filename'] = $download->getFileName();
		$data['category_id'] = $download->category['id'];
		$data['labels'] = array();

		foreach ($download->getLabelManager()->getLabelsArray() as $label) {
			$data['labels'][] = $label;
		}

		$doc = Document::newFromArray($data);

		return $doc;
	}
}