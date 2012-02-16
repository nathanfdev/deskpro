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

use Application\DeskPRO\App;
use Application\DeskPRO\ORM\Util\Util as ORM_Util;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Numbers;

use Application\DeskPRO\Entity\UsergroupPropertyPermission;
use Application\DeskPRO\Entity;


/**
 * Maps known company domains to their company objects
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\OrganizationEmailDomain")
 * @ORM_Mapping\Table(name="organization_email_domains")
 */
class OrganizationEmailDomain extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The email domain
	 *
	 * @var int
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="domain", type="string", length=255)
	 *
	 */
	protected $domain = null;

	/**
	 * The users organization
	 *
	 * @var \Application\DeskPRO\Entity\Organization
	 * @ORM_Mapping\ManyToOne(targetEntity="Organization")
	 * @ORM_Mapping\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $organization = null;

	public function __toString()
	{
		return $this->domain;
	}
}
