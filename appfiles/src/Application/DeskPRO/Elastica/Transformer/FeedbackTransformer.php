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

use Application\DeskPRO\Entity\Idea;

class IdeaTransformer implements TransformerInterface
{
	public function transform($feedback)
	{
		$data = array();
		$data['title'] = $feedback['title'];
		$data['content'] = $feedback['content'];
		$data['date_created'] = $feedback['date_created']->getTimestamp();
		$data['labels'] = $feedback->getLabelManager()->getLabelsArray();

		$data['category_id'] = $feedback->category['id'];

		$doc = new \Elastica_Document($feedback['id'], $data);

		return $doc;
	}
}