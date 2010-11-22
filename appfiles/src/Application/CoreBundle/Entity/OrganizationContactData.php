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

namespace Application\CoreBundle\Entity;

/**
 * Contact data for an organization
 *
 * @Entity
 * @Table(name="organizations_contact_data")
 */
class OrganizationContactData extends ContactDataAbstract
{
	/**
	 * @var int
	 * @Column(name="organization_id", type="integer")
	 */
	protected $organization_id;

	/**
	 * @var \Application\CoreBundle\Entity\Organization
	 * @ManyToOne(targetEntity="Organization")
	 * @JoinColumn(name="organization_id", referencedColumnName="id")
	 */
	protected $organization;
}