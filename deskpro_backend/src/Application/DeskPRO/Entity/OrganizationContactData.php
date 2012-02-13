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
 * Contact data for an organization
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="organizations_contact_data")
 */
class OrganizationContactData extends ContactDataAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\Organization
	 * @ORM_Mapping\ManyToOne(targetEntity="Organization")
	 * @ORM_Mapping\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $organization;
}