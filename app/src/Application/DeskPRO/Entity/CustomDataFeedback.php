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
 * Custom data
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="custom_data_feedback", indexes={
 *     @ORM_Mapping\Index(name="obj_id_idx", columns={"feedback_id"}),
 *     @ORM_Mapping\Index(name="field_id_idx", columns={"field_id","feedback_id"})
 * })
 */
class CustomDataFeedback extends CustomDataAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\Feedback
	 * @ORM_Mapping\ManyToOne(targetEntity="Feedback")
	 * @ORM_Mapping\JoinColumn(name="feedback_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $feedback;

	/**
	 * @var \Application\DeskPRO\Entity\CustomDefFeedback
	 * @ORM_Mapping\ManyToOne(targetEntity="CustomDefFeedback", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="field_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $field = null;

	public function getFeedbackId()
	{
		return $this->feedback['id'];
	}
}
