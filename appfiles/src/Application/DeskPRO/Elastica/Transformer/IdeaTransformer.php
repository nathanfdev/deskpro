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
	public function transform($idea)
	{
		$data = array();
		$data['title'] = $idea['title'];
		$data['content'] = $idea['content'];
		$data['date_created'] = $idea['date_created']->getTimestamp();
		//$data['labels'] = $idea->getLabelManager()->getLabelsArray();

		$data['category_id'] = $idea->category['id'];

		$doc = new \Elastica_Document($idea['id'], $data);

		return $doc;
	}
}