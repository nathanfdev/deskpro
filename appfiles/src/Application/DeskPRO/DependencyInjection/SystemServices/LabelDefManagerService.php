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
use Application\DeskPRO\Labels\LabelDefManager;

class LabelDefManagerService
{
	public static function create(DeskproContainer $container)
	{
		$s = new LabelDefManager(
			$container->get('doctrine.orm.entity_manager')
		);

		return $s;
	}
}
