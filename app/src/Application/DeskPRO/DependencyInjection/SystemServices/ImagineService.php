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

class ImagineService
{
	public static function create(DeskproContainer $container)
	{
		if (function_exists('gd_info')) {
			$im = new \Imagine\Gd\Imagine();
		} elseif (class_exists('Imagick', false)) {
			$im = new \Imagine\Imagick\Imagine();
		} elseif (class_exists('Gmagick', false)) {
			$im = new \Imagine\Gmagick\Imagine();
		} else {
			throw new \RuntimeException("Cannot create Imagine instance: No image manipulation extensions installed in PHP");
		}

		return $im;
	}
}
