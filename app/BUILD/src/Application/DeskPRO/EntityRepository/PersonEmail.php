<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Doctrine\DBAL\LockMode;

class PersonEmail extends AbstractEntityRepository
{
    public function getEmail($email_address)
    {
        if (App::getDb()->isTransactionActive()) {
            return $this->getEntityManager()->createQuery('
                SELECT e
                FROM DeskPRO:PersonEmail e
                WHERE e.email = ?1
            ')->setLockMode(LockMode::PESSIMISTIC_READ)->setParameters([1 => $email_address])->setMaxResults(1)->getOneOrNullResult();
        } else {
            return $this->getEntityManager()->createQuery('
                SELECT e
                FROM DeskPRO:PersonEmail e
                WHERE e.email = ?1
            ')->setParameters([1 => $email_address])->setMaxResults(1)->getOneOrNullResult();
        }
    }

    /**
     * Count the number of email addresses at one or more arrays.
     *
     * @param array|string $domains
     *
     * @return array|int
     */
    public function countDomains($domains)
    {
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

        $results = array_merge($results, $this->getEntityManager()->getConnection()->fetchAllKeyValue('
            SELECT email_domain, COUNT(*) as count
            FROM email_domain
            WHERE email_domain IN (?)
            GROUP BY email_domain
        ', [$domains], [Connection::PARAM_STR_ARRAY]));

        if ($single) {
            return array_pop($results);
        }

        return $results;
    }

    /**
     * Count the number of email addresses at one or more emails where the user belongs to a company
     * that isnt this one.
     *
     * @param array|string     $domains
     * @param int|Organization $org
     *
     * @return array|int
     */
    public function countDomainsWithOtherCompany($domains, $org)
    {
        $single = false;

        if (!$domains) {
            if (!is_array($domains)) {
                return 0;
            } else {
                return [];
            }
        }

        if (!is_array($domains)) {
            $domains = [$domains];
            $single  = true;
        }

        // Init all to zero
        $results = array_combine($domains, array_fill(0, count($domains), 0));
        $org     = is_object($org) ? $org->id : $org;

        $results = array_merge($results, $this->getEntityManager()->getConnection()->fetchAllKeyValue('
            SELECT people_emails.email_domain, COUNT(DISTINCT people.id) as count
            FROM people_emails
            JOIN people ON (people.id = people_emails.person_id)
            WHERE people_emails.email_domain IN (?) AND people.organization_id != ? AND people.organization_id IS NOT NULL
            GROUP BY people_emails.email_domain
        ', [$domains, $org], [Connection::PARAM_STR_ARRAY, \PDO::PARAM_INT]));

        if ($single) {
            return array_pop($results);
        }

        return $results;
    }

    /**
     * Count the number of email addresses at one or more emails where the user does not
     * belong to any company.
     *
     * @param array|string $domains
     *
     * @return array|int
     */
    public function countDomainsWithNoCompany($domains)
    {
        if (!$domains) {
            if (!is_array($domains)) {
                return 0;
            } else {
                return [];
            }
        }

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

        $domains = App::getDb()->quoteIn($domains);

        $results = array_merge($results, App::getDb()->fetchAllKeyValue("
            SELECT people_emails.email_domain, COUNT(DISTINCT people.id) as count
            FROM people_emails
            LEFT JOIN people ON (people.id = people_emails.person_id)
            WHERE people_emails.email_domain IN ($domains) AND people.id IS NOT NULL AND people.organization_id IS NULL
            GROUP BY people_emails.email_domain
        "));

        if ($single) {
            return array_pop($results);
        }

        return $results;
    }

    public function search($query, $limit = 10)
    {
        return $this->_em->getConnection()->fetchAll(
            sprintf('select id, email from %s where email like :email limit %d', $this->getTableName(), $limit),
            ['email' => '%'.mb_strtolower($query).'%']
        );
    }

    /**
     * @param string $term
     * @param int    $limit
     *
     * @return array
     */
    public function searchUserEmails($term, $limit = 10)
    {
        $qb = $this->createQueryBuilder('pe');
        $qb
            ->select('pe.email')
            ->join('pe.person', 'p')
            ->where('pe.email LIKE :term AND p.is_user = true AND p.is_agent = false')
            ->setParameter('term', "$term%")
            ->setMaxResults($limit);

        $result = $qb->getQuery()->getResult();
        foreach ($result as &$record) {
            $record = $record['email'];
        }

        return $result;
    }

    /**
     * @param string $term
     * @param int    $limit
     *
     * @return array
     */
    public function searchAgentEmails($term, $limit = 10)
    {
        $qb = $this->createQueryBuilder('pe');
        $qb
            ->select('pe.email')
            ->join('pe.person', 'p')
            ->where('pe.email LIKE :term AND p.is_agent = true')
            ->setParameter('term', "$term%")
            ->setMaxResults($limit);

        $result = $qb->getQuery()->getResult();
        foreach ($result as &$record) {
            $record = $record['email'];
        }

        return $result;
    }
}
