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

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Organization as OrganizationEntity;

use Orb\Util\Numbers;

class OrganizationEmailDomain extends \Doctrine\ORM\EntityRepository
{
	public function getDomainsForOrganization(OrganizationEntity $org)
	{
		$domains = App::getDb()->fetchAllCol("
			SELECT domain
			FROM organization_email_domains
			WHERE organization_id = ?
		", array($org->id));

		return $domains;
	}
}
