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

use Doctrine\ORM\EntityRepository;
use Application\DeskPRO\App;

class StatValueGroup extends EntityRepository
{
	public function getReferenceIdsByStat($stat_id)
	{
		$references = App::getDb()->fetchAllCol("
			SELECT grouping_id
			FROM stat_value_group svg
			INNER JOIN stat_value sv ON sv.id = svg.stat_value_id
			WHERE sv.stat_id = $stat_id
		");

		$referenceIds = array();
		foreach ($references as $reference) {
			if (false === is_null($reference['grouping_id'])) {
				$referenceIds[] = $reference['grouping_id'];
			}
		}

		return $referenceIds;
	}

}