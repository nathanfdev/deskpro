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
 * Comments on feedback
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\FeedbackComment")
 * @ORM_Mapping\Table(name="feedback_comments")
 */
class FeedbackComment extends CommentAbstract
{
	/**
	 * @ORM_Mapping\ManyToOne(targetEntity="Feedback", inversedBy="comment")
	 * @ORM_Mapping\JoinColumn(name="feedback_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $feedback;

	public function getObject()
	{
		return $this->feedback;
	}
}