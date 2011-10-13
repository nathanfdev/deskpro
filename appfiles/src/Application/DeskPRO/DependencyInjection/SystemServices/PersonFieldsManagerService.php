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

class PersonFieldsManagerService
{
	public static function create(DeskproContainer $container)
	{
		$m = new FieldManager(
			$container->get('doctrine.orm.entity_manager'),
			array(
				'entity_class'       => 'Application\\DeskPRO\\Entity\\CustomDefPerson',
				'entity_name'        => 'DeskPRO:CustomDefPerson',
				'data_entity_class'  => 'Application\\DeskPRO\\Entity\\CustomDataPerson',
				'data_entity_name'   => 'DeskPRO:CustomDataPerson',
			)
		);

		return $m;
	}
}
