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

use Orb\Util\Numbers;

class Widget extends EntityRepository
{
	public function getWidgetsForSection($section)
	{
		$likes = array();
		$params = array();

		$x = 0;
		foreach ((array)$section as $s) {
			$x++;
			$likes[] = "w.section LIKE ?$x";
			$params[$x] = $s . '%';
		}

		$likes = implode(' OR ', $likes);

		$widgets = $this->getEntityManager()->createQuery("
			SELECT w
			FROM DeskPRO:Widget w
			WHERE $likes
		")->setParameters($params)->execute();

		return $widgets;
	}
}
