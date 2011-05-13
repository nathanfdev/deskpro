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

class TicketPageDisplay extends EntityRepository
{
	public function getFromZone($zone, $department_context = null)
	{
		if ($department_context) {
			if (is_object($department_context)) {
				$department_context = $department_context['id'];
			}

			return $this->findBy(array('zone' => $zone, 'department_id' => $department_context))
		} else {
			return $this->findBy(array('zone' => $zone));
		}
	}
}