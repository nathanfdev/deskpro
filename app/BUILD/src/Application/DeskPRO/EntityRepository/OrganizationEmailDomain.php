<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Organization as OrganizationEntity;

class OrganizationEmailDomain extends AbstractEntityRepository
{
    /**
     * Get a org domain object by the domain.
     *
     * @param $domain
     *
     * @return \Application\DeskPRO\Entity\OrganizationEmailDomain
     */
    public function findByDomain($domain)
    {
        return $this->getEntityManager()->createQuery('
            SELECT d
            FROM DeskPRO:OrganizationEmailDomain d
            WHERE d.domain = ?1
        ')->setParameter(1, $domain)->getOneOrNullResult();
    }

    public function getDomainsForOrganization(OrganizationEntity $org)
    {
        $domains = $this->getEntityManager()->getConnection()->fetchAllCol('
            SELECT domain
            FROM organization_email_domains
            WHERE organization_id = ?
        ', [$org['id']]);

        return $domains;
    }

    /**
     * Count the number of emails that belong to a domain but arent members of an org.
     *
     * @param Organization|int $org
     * @param array|string     $domains
     *
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
     * @param array|string     $domains
     *
     * @return array|int
     */
    public function countMembersAtDomains($org, $domains)
    {
        return $this->_getCounts($org, $domains, '=');
    }

    protected function _getCounts($org, $domains, $op)
    {
        if (!$domains) {
            if (is_array($domains)) {
                return [];
            } else {
                return 0;
            }
        }

        $org_id = is_object($org) ? $org->id : $org;

        $single = false;
        if (!is_array($domains)) {
            $domains = [$domains];
            $single  = true;
        }

        // Init all to zero
        $results = array_combine($domains, array_fill(0, count($domains), 0));

        if (!$domains) {
            if ($single) {
                return 0;
            } else {
                return $results;
            }
        }

        /** @var Connection $conn */
        $conn = $this->getEntityManager()->getConnection();

        $results = array_merge($results, $conn->fetchAllKeyValue("
            SELECT people_emails.email_domain, COUNT(DISTINCT people.id) as count
            FROM people_emails
            JOIN people ON (people.id = people_emails.person_id)
            WHERE people_emails.email_domain IN (?) AND people.organization_id $op ?
            GROUP BY people_emails.email_domain
        ", [$domains, $org_id], [Connection::PARAM_STR_ARRAY, \PDO::PARAM_INT]));

        if ($single) {
            return array_pop($results);
        }

        return $results;
    }
}
