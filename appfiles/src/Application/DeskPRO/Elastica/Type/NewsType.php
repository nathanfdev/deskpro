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

use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Elastica\Transformer\NewsTransformer;

use APplication\DeskPRO\App;

/**
 * Type for the News entity
 */
class NewsType extends AbstractType
{
	/**
	 * Transform a value into a Document
	 *
	 * @param  Idea $news
	 * @return \Elastic_Document
	 */
	public function transformToDocument($news)
	{
		$trans = new NewsTransformer();
		$doc = $trans->transform($news);

		$doc->setIndex('content');
		$doc->setType('news');

		return $doc;
	}


	/**
	 * Get a single value from a document
	 *
	 * @return \Application\DeskPRO\Entity\News
	 */
	protected function getValueFromResult(\Elastica_Result $doc)
	{
		return App::getEntityRepository('DeskPRO:News')->find($doc->getId());
	}
}