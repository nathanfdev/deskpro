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
 * Labels on organizations
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="labels_organizations")
 */
class LabelOrganization extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var string
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="label", type="string", length=255)
	 */
	protected $label;

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
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