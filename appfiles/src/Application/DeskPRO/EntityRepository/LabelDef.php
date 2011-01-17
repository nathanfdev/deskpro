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

class LabelDef extends EntityRepository
{
	protected $department_names = null;

	/**
	 * Get the top counts for labels of a certain type.
	 *
	 * @return array
	 */
	public function getLabelCounts($type, $limit = 25)
	{
		// TODO this should be cached somehow, or probably
		// needs new column in LaeblDef to store counts statically, and then
		// add postInsert code to each label entity to increase the count automatically

		if ($type == 'ticket') {
			return $this->getEntityManager()->getConnection()->feetchAllKeyValue("
				SELECT label, COUNT(*) AS count
				FROM labels_tickets
				GROUP BY label
				ORDER BY count DESC
				LIMIT $limit
			");
		} else {
			throw new \InvalidArgumentException("`$type` is an invlaid label type");
		}
	}
}