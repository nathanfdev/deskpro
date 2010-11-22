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
 * Labels on organizations
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="labels_organizations")
 */
class LabelOrganization extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var string
	 * @Id
	 * @Column(name="label", type="string", length=255)
	 */
	protected $label;

	/**
	 * @var int
	 * @Id
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