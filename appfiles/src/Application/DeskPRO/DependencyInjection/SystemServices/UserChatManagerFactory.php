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
use Orb\Util\CheckedOptionsArray;
use Orb\Util\CheckedOptionsException;
use Application\DeskPRO\Chat\UserChat\UserChatManager;

class UserChatManagerFactory
{
	public static function create(DeskproContainer $container, CheckedOptionsArray $options)
	{
		$o = new UserChatManager(
			$options->session,
			$container->get('doctrine.orm.entity_manager'),
			$container->get('deskpro.core.translate')
		);

		return $o;
	}
}
