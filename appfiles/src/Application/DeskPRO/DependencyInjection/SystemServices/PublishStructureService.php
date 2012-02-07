<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category DependencyInjection
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

class PublishStructureService
{
	public static function create(DeskproContainer $container)
	{
		$structure = new \Application\DeskPRO\Publish\Structure(
			$container->getEm(),
			$container->getSystemService('publish_structure_cache')
		);

		return $structure;
	}
}
