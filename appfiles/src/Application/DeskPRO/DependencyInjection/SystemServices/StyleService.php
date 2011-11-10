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
use Application\DeskPRO\CustomFields\FieldManager;

class StyleService
{
	public static function create(DeskproContainer $container)
	{
		$style_id = $container->get('deskpro.core.settings')->get('core.default_style_id');
		$style = $container->get('doctrine.orm.entity_manager')->find('DeskPRO:Style', array('id' => $style_id));

		return $style;
	}
}
