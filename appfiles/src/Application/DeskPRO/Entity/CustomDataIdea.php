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
 * @ORM_Mapping\Table(name="custom_data_idea", indexes={
 *     @ORM_Mapping\Index(name="field_id_idx", columns={"field_id","idea_id"})
 * })
 */
class CustomDataIdea extends CustomDataAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\Idea
	 * @ORM_Mapping\ManyToOne(targetEntity="Idea")
	 * @ORM_Mapping\JoinColumn(name="idea_id", referencedColumnName="id", onDelete="cascade")
	 * @ORM_Mapping\Id
	 */
	protected $idea;

	/**
	 * @var \Application\DeskPRO\Entity\CustomDefIdea
	 * @var \Application\DeskPRO\Entity\CustomDefIdea
	 * @ORM_Mapping\ManyToOne(targetEntity="CustomDefIdea", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="field_id", referencedColumnName="id", onDelete="cascade")
	 * @ORM_Mapping\Id
	 */
	protected $field = null;

	public function getIdeaId()
	{
		return $this->idea['id'];
	}
}
