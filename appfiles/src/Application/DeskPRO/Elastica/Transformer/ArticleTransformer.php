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

use Application\DeskPRO\Entity\Article;

class ArticleTransformer implements TransformerInterface
{
	public function transform($article)
	{
		$data = array();
		$data['title'] = $article['title'];
		$data['content'] = $article['content'];
		$data['date_created'] = $article['date_created']->getTimestamp();
		//$data['labels'] = $article->getLabelManager()->getLabelsArray();

		$data['category_ids'] = array();
		foreach ($article->categories as $c) {
			$data['category_ids'][] = $c['id'];
		}

		$doc = new \Elastica_Document($article['id'], $data);

		return $doc;
	}
}