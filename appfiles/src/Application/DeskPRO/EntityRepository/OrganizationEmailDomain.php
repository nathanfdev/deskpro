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
	/**
	 * Get a org domain object by the domain
	 *
	 * @param $domain
	 * @return \Application\DeskPRO\Entity\OrganizationEmailDomain
	 */
	public function findByDomain($domain)
	{
		return $this->getEntityManager()->createQuery("
			SELECT d
			FROM DeskPRO:OrganizationEmailDomain d
			WHERE d.domain = ?1
		")->setParameter(1, $domain)->getOneOrNullResult();
	}

	public function getDomainsForOrganization(OrganizationEntity $org)
	{
		$domains = App::getDb()->fetchAllCol("
			SELECT domain
			FROM organization_email_domains
			WHERE organization_id = ?
		", array($org->id));

		return $domains;
	}

	/**
	 * Count the number of emails that belong to a domain but arent members of an org.
	 *
	 * @param Organization|int $org
	 * @param array|string $domains
	 * @return array|int
	 */
	public function countNonMembersAtDomains($org, $domains)
	{
		return $this->_getCounts($org, $domains, '!=');
	}


	/**
	 * Count the number of emails that belong to a domain and are members of an org.
	 *
	 * @param Organization|int $org
	 * @param array|string $domains
	 * @return array|int
	 */
	public function countMembersAtDomains($org, $domains)
	{
		return $this->_getCounts($org, $domains, '=');
	}

	protected function _getCounts($org, $domains, $op)
	{
		$org_id = is_object($org) ? $org : $org->id;

		$single = false;
		if (!is_array($domains)) {
			$domains = array($domains);
			$single = true;
		}

		// Init all to zero
		$results = array_combine($domains, array_fill(0, count($domains), 0));

		if (!$domains) {
			if ($is_single) {
				return 0;
			} else {
				return $results;
			}
		}

		$domains = App::getDb()->quoteIn($domains);

		$results = array_merge($results, App::getDb()->fetchAllKeyValue("
			SELECT people_emails.email_domain, COUNT(*) as count
			FROM people_emails
			LEFT JOIN people ON (people.id = people_emails.person_id)
			WHERE people_emails.email_domain IN ($domains) AND people.organization_id $op ?
			GROUP BY people_emails.email_domain
		", array($org_id)));

		if ($single) {
			return array_pop($results);
		}

		return $results;
	}
}
