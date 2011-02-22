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

class Template extends EntityRepository
{
	public function getTemplateForStyle($template_name, $style)
	{
		$r = $this->getEntityManager()->createQuery("
			SELECT t
			FROM DeskPRO:Template t
			WHERE t.style = ?1 AND t.path = ?2
		")->execute(array(1=>$style, 2=>$template_name));

		if (!count($r)) {
			return null;
		}

		return $r[0];
	}

	public function getCustomTemplateNamesInStyle($style)
	{
		$names = App::getDb()->fetchColumn("
			SELECT path
			FROM templates
			WHERE style_id = ?
		", array($style['id']));

		return $names;
	}
}