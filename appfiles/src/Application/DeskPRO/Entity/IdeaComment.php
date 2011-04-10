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
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\IdeaComment")
 * @orm:Table(name="idea_comments")
 */
class IdeaComment extends CommentAbstract
{
	/**
	 * @orm:ManyToOne(targetEntity="Idea", inversedBy="comment")
	 * @orm:JoinColumn(name="idea_id", referencedColumnName="id")
	 */
	protected $idea;
}