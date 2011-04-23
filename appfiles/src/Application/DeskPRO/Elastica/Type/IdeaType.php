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

namespace Application\DeskPRO\Elastica\Type;

use Application\DeskPRO\Entity\Idea;
use Application\DeskPRO\Elastica\Transformer\IdeaTransformer;

use APplication\DeskPRO\App;

/**
 * Type for the Idea entity
 */
class IdeaType extends AbstractType
{
	/**
	 * Transform a value into a Document
	 *
	 * @param  Idea $idea
	 * @return \Elastic_Document
	 */
	public function transformToDocument($idea)
	{
		$trans = new IdeaTransformer();
		$doc = $trans->transform($idea);

		$doc->setIndex('content');
		$doc->setType('idea');

		return $doc;
	}


	/**
	 * Get a single value from a document
	 *
	 * @return \Application\DeskPRO\Entity\Idea
	 */
	protected function getValueFromResult(\Elastica_Result $doc)
	{
		return App::getEntityRepository('DeskPRO:Idea')->find($doc->getId());
	}
}