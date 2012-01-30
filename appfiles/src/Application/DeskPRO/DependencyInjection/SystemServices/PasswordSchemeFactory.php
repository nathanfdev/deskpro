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

class PasswordSchemeFactory
{
	public static function create(DeskproContainer $container, $options = array())
	{
		if (empty($options['scheme'])) {
			throw new \InvalidArgumentException("Must have the `scheme` option set");
		}

		switch ($options['scheme']) {
			case 'deskpro3':
			case 'deskpro3_tech':
				$s = new \Application\DeskPRO\Import\PasswordScheme\Deskpro3PasswordScheme();
				return $s;
				break;

			default:
				throw new \InvalidArgumentException("Unknown password scheme `{$options['scheme']}`");
		}
	}
}
