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
use Application\DeskPRO\CustomFields\TicketFieldManager;

class TicketFieldsManagerService
{
	public static function create(DeskproContainer $container)
	{
		$m = new TicketFieldManager(
			$container->get('doctrine.orm.entity_manager'),
			array(
				'entity_class'       => 'Application\\DeskPRO\\Entity\\CustomDefTicket',
				'entity_name'        => 'DeskPRO:CustomDefTicket',
				'data_entity_class'  => 'Application\\DeskPRO\\Entity\\CustomDataTicket',
				'data_entity_name'   => 'DeskPRO:CustomDataTicket',
			)
		);

		return $m;
	}
}
