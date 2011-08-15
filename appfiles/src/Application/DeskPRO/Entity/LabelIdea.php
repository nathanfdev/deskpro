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

/**
 * Labels on tickets
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="labels_ideas")
 */
class LabelIdea extends LabelAssocAbstract
{
	const LABEL_TYPENAME = 'ideas';

	/**
	 * @var \Application\DeskPRO\Entity\Idea
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\ManyToOne(targetEntity="Idea")
	 * @ORM_Mapping\JoinColumn(name="idea_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $idea;
}