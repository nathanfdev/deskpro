<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Elastica
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Elastica\Transformer;

use Application\DeskPRO\Entity\Download;

class DownloadTransformer implements TransformerInterface
{
	public function transform($download)
	{
		$data = array();
		$data['title'] = $download['title'];
		$data['content'] = $download['content'];
		$data['date_created'] = $download['date_created']->getTimestamp();
		$data['labels'] = $download->getLabelManager()->getLabelsArray();

		$data['category_id'] = $download->category['id'];

		$doc = new \Elastica_Document($download['id'], $data);

		return $doc;
	}
}