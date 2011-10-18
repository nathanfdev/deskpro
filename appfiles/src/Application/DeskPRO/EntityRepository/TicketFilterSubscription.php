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

use \Doctrine\ORM\EntityRepository;
use Application\DeskPRO\Entity\Person as PersonEntity;

class TicketFilterSubscription extends EntityRepository
{
	public function getForAgent(PersonEntity $person)
	{
		$results = $this->getEntityManager()->createQuery("
			SELECT s
			FROM DeskPRO:TicketFilterSubscription s
			LEFT JOIN s.filter f
			WHERE s.person = ?1
		")->execute(array(1=> $person));

		$ret = array();

		foreach ($results as $s) {
			$ret[$s->filter->id] = $s;
		}

		return $ret;
	}
}
