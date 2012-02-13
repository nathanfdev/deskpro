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

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Elastica\Transformer\ArticleTransformer;

use APplication\DeskPRO\App;

/**
 * Type for the Article entity
 */
class ArticleType extends AbstractType
{
	/**
	 * Transform a value into a Document
	 *
	 * @param  Article $article
	 * @return \Elastic_Document
	 */
	public function transformToDocument($article)
	{
		$trans = new ArticleTransformer();
		$doc = $trans->transform($article);

		$doc->setIndex('content');
		$doc->setType('article');

		return $doc;
	}

	
	/**
	 * Get the document type
	 *
	 * @return string
	 */
	public function getType()
	{
		return 'article';
	}


	/**
	 * Get a single value from a document
	 *
	 * @return \Application\DeskPRO\Entity\Article
	 */
	protected function getValueFromResult(\Elastica_Result $doc)
	{
		return App::getEntityRepository('DeskPRO:Article')->find($doc->getId());
	}
}