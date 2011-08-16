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

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

use \Orb\Util\Numbers;

class Organization extends EntityRepository
{
	protected $_organization_names = null;

	public function findOneByName($name)
	{
		return $this->getEntityManager()->createQuery("
			SELECT o
			FROM DeskPRO:Organization o
			WHERE o.name = ?1
		")->setParamter(1, $name)->getOneOrNullResult();
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
        foreach ($for_ids as $id) {
            $ret[$id] = $this->_organization_names[$id];
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