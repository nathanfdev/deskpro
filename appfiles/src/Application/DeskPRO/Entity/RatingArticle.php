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
 * Ratings on articles
 *
 * @orm:Entity
 * @orm:Table(name="ratings_article")
 */
class RatingArticle extends RatingAbstract
{
	/**
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Article", inversedBy="comment")
	 * @orm:JoinColumn(name="article_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $article;
}