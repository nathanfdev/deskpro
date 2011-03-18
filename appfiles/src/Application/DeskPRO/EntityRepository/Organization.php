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
	protected $organization_names = null;

	/**
	 * @return array
	 */
	public function getOrganizationNames($for_ids = null)
	{
		if ($this->organization_names == null) {

            $db = App::getDb();
            $this->organization_names = $db->fetchAllKeyValue("
                SELECT id, name
                FROM organizations
                ORDER BY name ASC
            ");
        }

        if ($for_ids === null) {
		    return $this->organization_names;
        }

        $ret = array();
        foreach ($for_ids as $id) {
            $ret[$id] = $this->organization_names[$id];
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
}