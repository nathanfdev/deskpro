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
 * Ratings on ideas
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="ratings_idea")
 */
class RatingIdea extends RatingAbstract
{
	/**
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\ManyToOne(targetEntity="Idea", inversedBy="comment")
	 * @ORM_Mapping\JoinColumn(name="idea_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $idea;
}