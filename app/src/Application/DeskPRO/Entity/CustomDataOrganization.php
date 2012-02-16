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
 * Custom organization data
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="custom_data_organizations", indexes={
 *     @ORM_Mapping\Index(name="obj_id_idx", columns={"organization_id"}),
 *     @ORM_Mapping\Index(name="field_id_idx", columns={"field_id","organization_id"})
 * })
 */
class CustomDataOrganization extends CustomDataAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\Organization
	 * @ORM_Mapping\ManyToOne(targetEntity="Organization")
	 * @ORM_Mapping\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $organization;

	/**
	 * @var \Application\DeskPRO\Entity\CustomDefOrganization
	 * @ORM_Mapping\ManyToOne(targetEntity="CustomDefOrganization")
	 * @ORM_Mapping\JoinColumn(name="field_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $field = null;

	public function getOrganizationId()
	{
		return $this->organization['id'];
	}
}
