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
 * @ORM_Mapping\Table(name="labels_feedback")
 */
class LabelFeedback extends LabelAssocAbstract
{
	const LABEL_TYPENAME = 'feedback';

	/**
	 * @var \Application\DeskPRO\Entity\Feedback
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\ManyToOne(targetEntity="Feedback")
	 * @ORM_Mapping\JoinColumn(name="feedback_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $feedback;
}