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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Comments on articles
 *
 * @orm:Entity
 * @orm:Table(name="comments_article")
 */
class CommentArticle extends CommentAbstract
{
	/**
	 * @orm:ManyToOne(targetEntity="Article", inversedBy="comment")
	 * @orm:JoinColumn(name="article_id", referencedColumnName="id")
	 */
	protected $article;
}