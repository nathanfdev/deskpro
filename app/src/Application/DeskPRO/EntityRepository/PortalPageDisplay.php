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
use Orb\Util\Arrays;

class PortalPageDisplay extends AbstractEntityRepository
{
	public function getEnabledBlocks()
	{
		return $this->getEntityManager()->createQuery("
			SELECT p
			FROM DeskPRO:PortalPageDisplay p
			WHERE p.is_enabled = 1
			ORDER BY p.display_order ASC
		")->execute();
	}

	public function getAllBlocks()
	{
		return $this->getEntityManager()->createQuery("
			SELECT p
			FROM DeskPRO:PortalPageDisplay p
			ORDER BY p.display_order ASC
		")->execute();
	}
}
