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
 * Attributes or various other fields that are searchable on some type
 *
 * @orm:Entity
 * @orm:Table(name="content_search_attribute")
 */
class ContentSearchAttribute extends CommentAbstract
{
	/**
	 * The search ID like "article.11" or "idea.22"
	 *
	 * @orm:Id
	 * @orm:Column(name="content_id", type="string", length=200)
	 */
	protected $content_id;

	/**
	 * The name of the attribute like "somefield"
	 * 
	 * @orm:Id
	 * @orm:Column(name="attribute_id", type="string", length=200)
	 */
	protected $attribute_id;

	/**
	 * The searchable content of the attribuet
	 * 
	 * @orm:Id
	 * @orm:Column(name="content", type="text")
	 */
	protected $content;
}