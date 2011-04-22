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

use Application\DeskPRO\Entity\News;

class NewsTransformer implements TransformerInterface
{
	public function transform(News $news)
	{
		$data = array();
		$data['title'] = $news['title'];
		$data['content'] = $news['content'];
		$data['date_created'] = $news['date_created']->getTimestamp();
		$data['labels'] = $news->getLabelManager()->getLabelsArray();

		$data['category_id'] = $news['category_id'];

		$doc = new \Elastica_Document($news['id'], $data);

		return $doc;
	}
}