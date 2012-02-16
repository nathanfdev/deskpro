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

class Organization extends AbstractEntityRepository
{
	protected $_organization_names = null;

	public function findOneByName($name)
	{
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('o')
            ->from('DeskPRO:Organization', 'o')
            ->where('o.name = :name')
            ->setParameter('name', $name);

            $query = $qb->getQuery();
            return $query->getOneOrNullResult();
	}

	/**
	 * @return array
	 */
	public function getOrganizationNames($for_ids = null)
	{
		if ($this->_organization_names == null) {

			if (($this->_organization_names = App::getCache('common')->load('organization_names')) === false) {
				$db = App::getDb();
				$this->_organization_names = $db->fetchAllKeyValue("
					SELECT id, name
					FROM organizations
					ORDER BY name ASC
				");

				App::getCache('common')->save($this->_organization_names, null, array('organizations'));
			}
        }

        if ($for_ids === null) {
		    return $this->_organization_names;
        }

        $ret = array();
        foreach ((array)$for_ids as $id) {
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

		if (!$ids) return array();

		$orgs = $this->getEntityManager()->createQuery("
			SELECT o
			FROM DeskPRO:Organization o INDEX BY o.id
			WHERE o.id IN(" . implode(',', $ids) . ")
			ORDER BY o.id ASC
		")->execute();

		return $orgs;
	}

	/**
	 * Get a count of how many orgs there are
	 *
	 * @return int
	 */
	public function getCount()
	{
		return App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM organizations
		");
	}

	/**
	 * Count how many people there are in an organization
	 *
	 * @param \Application\DeskPRO\Entity\Organization $org
	 * @return int
	 */
	public function countMembersFor(OrganizationEntity $org)
	{
		return App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM people
			WHERE organization_id = {$org['id']}
		");
	}


	/**
	 * Fetch an organization by its name.
	 *
	 * @param string $name
	 * @return \Application\DeskPRO\Entity\Organization
	 */
	public function getByName($name)
	{
		$name = trim($name);

		return $this->getEntityManager()->createQuery("
			SELECT o
			FROM DeskPRO:Organization o
			WHERE
				o.name = ?1
		")->setParameter(1, $name)->setMaxResults(1)->getOneOrNullResult();
	}


	/**
	 * Invalidates caches
	 */
	public function invalidateCaches()
	{
		App::getCache('common')->clean('matchingTag', array('organizations'));
	}

	/**
	 * @see \Application\DeskPRO\DBAL\Logging\CacheInvalidor
	 * @param  $sql
	 * @return void
	 */
	public function invalidateFromQuery($sql)
	{
		$this->invalidateCaches();
	}
}
