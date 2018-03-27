<?php

/**
 * DeskPRO.
 *
 * @category Mail
 */

namespace Application\DeskPRO\Organizations;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationEmailDomain;
use Doctrine\ORM\EntityManager;

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
     *
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
     * Assign a domain to an org.
     */
    public function assignDomain($domain, Organization $org)
    {
        $domain = ltrim($domain, '@');

        $orgdomain               = new OrganizationEmailDomain();
        $orgdomain->domain       = $domain;
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
            // Update tickets
            $this->db->executeUpdate('
                UPDATE tickets
                LEFT JOIN people ON (people.id = tickets.person_id)
                LEFT JOIN people_emails ON (people_emails.person_id = people.id)
                SET tickets.organization_id = ?
                WHERE people.organization_id IS NULL AND people_emails.email_domain = ?
            ', [$orgdomain->organization->id, $orgdomain->domain]);

            $this->db->executeUpdate('
                UPDATE tickets_search_active
                LEFT JOIN people ON (people.id = tickets_search_active.person_id)
                LEFT JOIN people_emails ON (people_emails.person_id = people.id)
                SET tickets_search_active.organization_id = ?
                WHERE people.organization_id IS NULL AND people_emails.email_domain = ?
            ', [$orgdomain->organization->id, $orgdomain->domain]);

            $count = $this->db->executeUpdate('
                UPDATE people
                LEFT JOIN people_emails ON (people_emails.person_id = people.id)
                SET people.organization_id = ?
                WHERE people.organization_id IS NULL AND people_emails.email_domain = ?
            ', [$orgdomain->organization->id, $orgdomain->domain]);

            $this->em->flush();
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
            $this->db->executeUpdate('
                UPDATE tickets
                LEFT JOIN people ON (people.id = tickets.person_id)
                LEFT JOIN people_emails ON (people_emails.person_id = people.id)
                SET tickets.organization_id = ?
                WHERE people.organization_id IS NOT NULL AND people_emails.email_domain = ?
            ', [$orgdomain->organization->id, $orgdomain->domain]);

            $this->db->executeUpdate('
                UPDATE tickets_search_active
                LEFT JOIN people ON (people.id = tickets_search_active.person_id)
                LEFT JOIN people_emails ON (people_emails.person_id = people.id)
                SET tickets_search_active.organization_id = ?
                WHERE people.organization_id IS NOT NULL AND people_emails.email_domain = ?
            ', [$orgdomain->organization->id, $orgdomain->domain]);

            $count = $this->db->executeUpdate('
                UPDATE people
                LEFT JOIN people_emails ON (people_emails.person_id = people.id)
                SET people.organization_id = ?
                WHERE people.organization_id IS NOT NULL AND people_emails.email_domain = ?
            ', [$orgdomain->organization->id, $orgdomain->domain]);

            $this->em->flush();
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
                $this->db->executeUpdate('
                    UPDATE tickets
                    LEFT JOIN people ON (people.id = tickets.person_id)
                    LEFT JOIN people_emails ON (people_emails.person_id = people.id)
                    SET tickets.organization_id = ?
                    WHERE people.organization_id = ? AND people_emails.email_domain = ?
                ', [null, $orgdomain->organization->id, $orgdomain->domain]);

                $this->db->executeUpdate('
                    UPDATE tickets_search_active
                    LEFT JOIN people ON (people.id = tickets_search_active.person_id)
                    LEFT JOIN people_emails ON (people_emails.person_id = people.id)
                    SET tickets_search_active.organization_id = ?
                    WHERE people.organization_id = ? AND people_emails.email_domain = ?
                ', [null, $orgdomain->organization->id, $orgdomain->domain]);

                $count = $this->db->executeUpdate('
                    UPDATE people
                    LEFT JOIN people_emails ON (people_emails.person_id = people.id)
                    SET people.organization_id = NULL
                    WHERE people.organization_id = ? AND people_emails.email_domain = ?
                ', [$orgdomain->organization->id, $orgdomain->domain]);
            }

            $this->em->remove($orgdomain);
            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        return $count;
    }
}
