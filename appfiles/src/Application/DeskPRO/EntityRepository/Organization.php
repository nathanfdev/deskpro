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

class Organization extends EntityRepository
{
	protected $organization_names = null;

	/**
	 * @return array
	 */
	public function getOrganizationNames()
	{
		if ($this->organization_names !== null) return $this->organization_names;

		$db = App::getDb();
		$this->organization_names = $db->fetchAllKeyValue("
			SELECT id, name
			FROM organizations
			ORDER BY name ASC
		");

		return $this->organization_names;
	}
}