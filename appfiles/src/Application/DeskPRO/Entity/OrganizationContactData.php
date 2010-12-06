<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\DeskPRO\Entity;

/**
 * Contact data for an organization
 *
 * @orm:Entity
 * @orm:Table(name="organizations_contact_data")
 */
class OrganizationContactData extends ContactDataAbstract
{
	/**
	 * @var int
	 * @orm:Column(name="organization_id", type="integer")
	 */
	protected $organization_id;

	/**
	 * @var \Application\DeskPRO\Entity\Organization
	 * @orm:ManyToOne(targetEntity="Organization")
	 * @orm:JoinColumn(name="organization_id", referencedColumnName="id")
	 */
	protected $organization;
}