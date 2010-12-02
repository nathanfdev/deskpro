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

use \DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class TicketCategory extends EntityRepository
{
	protected $all_category_names = null;

	/**
	 * @return array
	 */
	public function getAllCategoryNames()
	{
		if ($this->all_category_names !== null) return $this->all_category_names;

		$db = App::getDb();
		$this->all_category_names = $db->feetchAllKeyValue("
			SELECT cat.id, CONCAT(dep.title, ': ', cat.title)
			FROM ticket_categories cat
			LEFT JOIN departments AS dep ON (cat.department_id = dep.id)
			ORDER BY dep.title ASC, cat.title ASC
		");

		return $this->all_category_names;
	}
}