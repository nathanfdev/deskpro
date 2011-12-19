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
use Application\DeskPRO\EmailGateway\AddressMatcher;

class GatewayAddressMatcherService
{
	public static function create(DeskproContainer $container)
	{
		$m = new AddressMatcher(
			$container->get('doctrine.orm.entity_manager')
		);

		return $m;
	}
}
