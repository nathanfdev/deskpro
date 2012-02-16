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

class Template extends EntityRepository
{
	public function getTemplateForStyle($template_name, $style = null)
	{
		try {
			if ($style === null OR $style === 0) {
				$q = $this->getEntityManager()->createQuery("
					SELECT t
					FROM DeskPRO:Template t
					WHERE t.style IS NULL AND t.path = ?1
				")->setParameters(array(1=>$template_name));
			} else {
				$q = $this->getEntityManager()->createQuery("
					SELECT t
					FROM DeskPRO:Template t
					WHERE t.style = ?1 AND t.path = ?2
				")->setParameters(array(1=>$style, 2=>$template_name));
			}

			$r = $q->getSingleResult();
			return $r;
		} catch (\Exception $e) {
			return null;
		}
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
