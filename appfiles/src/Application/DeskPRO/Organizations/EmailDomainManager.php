<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Mail
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Organizations;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationEmailDomain;

use Doctrine\ORM\EntityManager;

use Orb\Util\Strings;
use Orb\Util\Util;

class EmailDomainManager
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->db = $em->getConnection();
	}

	/**
	 * Check to see if a domain is currently being used by soem other company.
	 *
	 * @param $domain
	 * @return bool
	 */
	public function isInUse($domain)
	{
		$orgdomain = $this->em->getRepository('DeskPRO:OrganizationEmailDomain')->findByDomain($domain);
		if ($orgdomain) {
			return $orgdomain->organization;
		}

		return false;
	}

	/**
	 * Assign a domain to an org
	 */
	public function assignDomain($domain, Organization $org)
	{
		$orgdomain = new OrganizationEmailDomain();
		$orgdomain->domain = $domain;
		$orgdomain->organization = $org;

		$this->em->beginTransaction();
		try {
			$this->em->persist($orgdomain);
			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		return $orgdomain;
	}

	public function moveNonCompanyUsers(OrganizationEmailDomain $orgdomain)
	{
		$this->em->beginTransaction();
		try {
			$count = $this->db->executeUpdate("
				UPDATE people
				LEFT JOIN people_emails ON (people_emails.id = people.id)
				SET people.organization_id = ?
				WHERE people.organization_id IS NULL AND people_emails.email_domain = ?
			", array($orgdomain->organization->id, $orgdomain->domain));
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		return $count;
	}

	public function moveOtherCompanyUsers(OrganizationEmailDomain $orgdomain)
	{
		$this->em->beginTransaction();
		try {
			$count = $this->db->executeUpdate("
				UPDATE people
				LEFT JOIN people_emails ON (people_emails.id = people.id)
				SET people.organization_id = ?
				WHERE people.organization_id IS NOT NULL AND people_emails.email_domain = ?
			", array($orgdomain->organization->id, $orgdomain->domain));
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		return $count;
	}

	public function unassignDomain($orgdomain, $remove_users = false)
	{
		$this->em->beginTransaction();

		try {
			$count = 0;
			if ($remove_users) {
				$count = $this->db->executeUpdate("
					UPDATE people
					LEFT JOIN people_emails ON (people_emails.id = people.id)
					SET people.organization_id = NULL
					WHERE people.organization_id = ? AND people_emails.email_domain = ?
				", array($orgdomain->organization->id, $orgdomain->domain));
			}

			$this->em->remove($orgdomain);
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		return $count;
	}
}
