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

class DealFieldsManagerService
{
	public static function create(DeskproContainer $container)
	{
		$m = new FieldManager(
			$container->get('doctrine.orm.entity_manager'),
			array(
				'entity_class'       => 'Application\\DeskPRO\\Entity\\CustomDefDeal',
				'entity_name'        => 'DeskPRO:CustomDefDeal',
				'data_entity_class'  => 'Application\\DeskPRO\\Entity\\CustomDataDeal',
				'data_entity_name'   => 'DeskPRO:CustomDataDeal',
			)
		);

		return $m;
	}
}
