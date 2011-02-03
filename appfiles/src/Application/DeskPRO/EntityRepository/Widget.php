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

class Widget extends EntityRepository
{
	public function getWidgetsForSection($section)
	{
		$widgets = $this->getEntityManager()->createQuery("
			SELECT w
			FROM DeskPRO:Widget w
			WHERE w.section LIKE ?1
		")->setParameter(1, $section . '%')->execute();

		return $widgets;
	}
}