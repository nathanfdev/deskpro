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
 * Combined search index of content
 *
 * @orm:Entity
 * @orm:Table(name="content_search")
 */
class ContentSearch extends CommentAbstract
{
	/**
	 * The combind unique ID. This is concatenation of object_type and object_id,
	 * for example "article.11" or "idea.22"
	 *
	 * @orm:Id
	 * @orm:Column(name="id", type="string", length=200)
	 */
	protected $id;

	/**
	 * @orm:Id
	 * @orm:Column(name="content", type="text")
	 */
	protected $content;
}