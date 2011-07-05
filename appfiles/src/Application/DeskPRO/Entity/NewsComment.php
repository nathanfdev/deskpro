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
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\NewsComment")
 * @orm:Table(name="news_comments")
 */
class NewsComment extends CommentAbstract
{
	/**
	 * @orm:ManyToOne(targetEntity="News", inversedBy="comment")
	 * @orm:JoinColumn(name="news_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $news;
}