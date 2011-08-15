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

use Doctrine\ORM\Mapping as ORM_Mapping;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Comments on ideas
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\IdeaComment")
 * @ORM_Mapping\Table(name="idea_comments")
 */
class IdeaComment extends CommentAbstract
{
	/**
	 * @ORM_Mapping\ManyToOne(targetEntity="Idea", inversedBy="comment")
	 * @ORM_Mapping\JoinColumn(name="idea_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $idea;

	public function getObject()
	{
		return $this->idea;
	}
}