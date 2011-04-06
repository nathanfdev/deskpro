<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

/**
 * Labels on tickets
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="labels_articles")
 */
class LabelArticle extends LabelAssocAbstract
{
	const LABEL_TYPENAME = 'articles';
	
	/**
	 * @var \Application\DeskPRO\Entity\Article
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Article")
	 * @orm:JoinColumn(name="article_id", referencedColumnName="id")
	 */
	protected $article;
}