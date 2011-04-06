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
 * Comments on ideas
 *
 * @orm:Entity
 * @orm:Table(name="comments_ida")
 */
class CommentIdea extends CommentAbstract
{
	/**
	 * @orm:ManyToOne(targetEntity="Idea", inversedBy="comment")
	 * @orm:JoinColumn(name="ida_id", referencedColumnName="id")
	 */
	protected $idea;
}