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
	 * @var string
	 * @orm:Column(name="object_type", type="string", length=100)
	 * @orm:Id
	 */
	protected $object_type;

	/**
	 * @var int
	 * @orm:Column(name="object_id", type="integer")
	 * @orm:Id
	 */
	protected $object_id = null;

	/**
	 * @orm:Id
	 * @orm:Column(name="content", type="text")
	 */
	protected $content;
}