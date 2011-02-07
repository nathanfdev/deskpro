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

/**
 * Custom organization data
 *
 * @orm:Entity
 * @orm:Table(name="custom_data_organizations")
 */
class CustomDataOrganization extends CustomDataAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\CustomDefOrganization
	 * @orm:ManyToOne(targetEntity="CustomDefOrganization")
	 * @orm:JoinColumn(name="field_id", referencedColumnName="id")
	 */
	protected $field = null;

	/**
	 * @var \Application\DeskPRO\Entity\Organization
	 * @orm:ManyToOne(targetEntity="Organization")
	 * @orm:JoinColumn(name="organization_id", referencedColumnName="id")
	 */
	protected $organization;

	public function getOrganizationId()
	{
		return $this->organization['id'];
	}
}