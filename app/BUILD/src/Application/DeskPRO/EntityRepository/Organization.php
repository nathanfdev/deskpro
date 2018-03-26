<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Organization as OrganizationEntity;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use Orb\Util\Numbers;

class Organization extends AbstractEntityRepository
{
    /** @var array|null */
    protected $_organization_names = null;
    protected $_has_any            = null;
    protected $_org_count          = null;

    /**
     * @param string $name
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return OrganizationEntity
     */
    public function findOneByName($name)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('o')
            ->from('DeskPRO:Organization', 'o')
            ->where('LOWER(o.name) = :name')
            ->setParameter('name', mb_strtolower($name))
            ->setMaxResults(1);

        $query = $qb->getQuery();
        if ($res = $query->getResult()) {
            return reset($res);
        }

        return;
    }

    public function hasOrganizations()
    {
        if ($this->_has_any !== null) {
            return $this->_has_any;
        }
        if ($this->_org_count !== null) {
            $this->_has_any = $this->_org_count > 0;
        }

        if ($this->_has_any === null) {
            $this->_has_any = ($this->_em->getConnection()->fetchColumn('
                SELECT COUNT(*)
                FROM organizations
                LIMIT 1
            ') > 0);
        }

        return $this->_has_any;
    }

    /**
     * @return array
     */
    public function getOrganizationNames($for_ids = null)
    {
        if (!$for_ids) {
            // Calling without for_ids is depreciated because there might be hundreds of thousands
            return [];
        }

        if ($this->_organization_names == null) {
            $db                        = $this->getEntityManager()->getConnection();
            $this->_organization_names = $db->fetchAllKeyValue('
                SELECT id, name
                FROM organizations
                ORDER BY name ASC
            ');
        }

        if ($for_ids === null) {
            return $this->_organization_names;
        }

        $ret = [];
        foreach ((array) $for_ids as $id) {
            if (isset($this->_organization_names[$id])) {
                $ret[$id] = $this->_organization_names[$id];
            }
        }

        return $ret;
    }

    public function getOrganizationsFromIds(array $ids)
    {
        // Only valid ID's please :)
        // Do this because Doctrine doesnt have proper IN()
        // escaping until 2.1
        $ids = array_filter($ids, function ($val) {
            if (Numbers::isInteger($val)) {
                return true;
            }

            return false;
        });

        if (!$ids) {
            return [];
        }

        $orgs = $this->getEntityManager()->createQuery('
            SELECT o
            FROM DeskPRO:Organization o INDEX BY o.id
            WHERE o.id IN(?0)
            ORDER BY o.id ASC
        ')->execute([$ids]);

        return $orgs;
    }

    /**
     * Get a count of how many orgs there are.
     *
     * @return int
     */
    public function getCount()
    {
        if ($this->_org_count !== null) {
            return $this->_org_count;
        }

        $this->_org_count = App::getDb()->fetchColumn('
            SELECT COUNT(*)
            FROM organizations
        ');

        return $this->_org_count;
    }

    /**
     * Count how many people there are in an organization.
     *
     * @param \Application\DeskPRO\Entity\Organization $org
     *
     * @return int
     */
    public function countMembersFor(OrganizationEntity $org)
    {
        if (!$org->id) {
            return 0;
        }

        return App::getDb()->fetchColumn("
            SELECT COUNT(*)
            FROM people
            WHERE organization_id = {$org['id']} AND is_deleted = false
        ");
    }

    /**
     * @return array
     */
    public function countMembers(array $orgs)
    {
        $ids = array_map(function ($a) {
            return $a->id;
        }, $orgs);

        return App::getDb()->fetchAllKeyValue('
            SELECT organization_id as id, COUNT(*) as count
            FROM people
            WHERE organization_id IN (?) AND is_deleted = 0
            GROUP BY organization_id
        ', [$ids], [Connection::PARAM_INT_ARRAY]);
    }

    /**
     * Gets the list of organization managers.
     *
     * @param \Application\DeskPRO\Entity\Organization $org
     *
     * @return \Application\DeskPRO\Entity\Person[]
     */
    public function getManagers(OrganizationEntity $org)
    {
        return $this->getEntityManager()->createQuery('
            SELECT p
            FROM DeskPRO:Person p
            WHERE p.organization = ?1 AND p.organization_manager = 1
            ORDER BY p.last_name, p.first_name
        ')->execute([1 => $org]);
    }

    /**
     * Fetch an organization by its name.
     *
     * @param string $name
     *
     * @return \Application\DeskPRO\Entity\Organization
     */
    public function getByName($name)
    {
        $name = trim($name);

        return $this->getEntityManager()->createQuery('
            SELECT o
            FROM DeskPRO:Organization o
            WHERE
                o.name = ?1
        ')->setParameter(1, $name)->setMaxResults(1)->getOneOrNullResult();
    }

    /**
     * @param $q
     * @param null $limit
     *
     * @return \Application\DeskPRO\Entity\Organization[]
     */
    public function search($q, $limit = null, $hydrate = true)
    {
        $q    = '%'.str_replace(['%', '_'], ['\\\\%', '\\\\_'], $q).'%';
        $q    = strtolower($q);
        $mode = $hydrate ? null : Query::HYDRATE_ARRAY;

        return $this->getEntityManager()->createQuery('
            SELECT o
            FROM DeskPRO:Organization o
            WHERE LOWER(o.name) LIKE ?1
            ORDER BY o.name ASC
        ')->setMaxResults($limit)->execute([1 => $q], $mode);
    }

    public function getOrgMembers(OrganizationEntity $org, $limit = 15)
    {
        return $this->getEntityManager()->createQuery('
            SELECT p FROM DeskPRO:Person p WHERE p.organization = :org
        ')->setParameter('org', $org)->setMaxResults((int) $limit)->getResult();
    }
}
