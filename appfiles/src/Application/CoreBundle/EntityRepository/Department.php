<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\EntityRepository;

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class Department extends EntityRepository
{
	protected $department_names = null;

	/**
	 * @return array
	 */
	public function getDepartmentNames()
	{
		if ($this->department_names !== null) return $this->department_names;

		$db = App::getDb();
		$this->department_names = $db->feetchAllKeyValue("
			SELECT id, title
			FROM departments
			ORDER BY title ASC
		");

		return $this->department_names;
	}
}