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
 * Custom ticket data
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="custom_data_deal",
 *     indexes={
 *         @ORM_Mapping\Index(name="field_id_idx", columns={"field_id","deal_type_id"})
 * })
 */
class CustomDataDeal extends CustomDataAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\DealType
	 * @ORM_Mapping\ManyToOne(targetEntity="DealType")
	 * @ORM_Mapping\JoinColumn(name="deal_type_id", referencedColumnName="id", onDelete="cascade")
	 * @ORM_Mapping\Id
	 */
	protected $deal_type;

	/**
	 * @var \Application\DeskPRO\Entity\CustomDefDeal
	 * @ORM_Mapping\ManyToOne(targetEntity="CustomDefDeal", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="field_id", referencedColumnName="id", onDelete="cascade")
	 * @ORM_Mapping\Id
	 */
	protected $field = null;

	public function getDealTypeId()
	{
		return $this->deal_type['id'];
	}
}
