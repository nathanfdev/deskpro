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

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Elastica\Transformer\FeedbackTransformer;

use APplication\DeskPRO\App;

/**
 * Type for the Feedback entity
 */
class FeedbackType extends AbstractType
{
	/**
	 * Transform a value into a Document
	 *
	 * @param  Feedback $feedback
	 * @return \Elastic_Document
	 */
	public function transformToDocument($feedback)
	{
		$trans = new FeedbackTransformer();
		$doc = $trans->transform($feedback);

		$doc->setIndex('content');
		$doc->setType('feedback');

		return $doc;
	}


	/**
	 * Get the document type
	 *
	 * @return string
	 */
	public function getType()
	{
		return 'feedback';
	}


	/**
	 * Get a single value from a document
	 *
	 * @return \Application\DeskPRO\Entity\Feedback
	 */
	protected function getValueFromResult(\Elastica_Result $doc)
	{
		return App::getEntityRepository('DeskPRO:Feedback')->find($doc->getId());
	}
}