<?php
namespace Application\DeskPRO\Widgets;

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Widgets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

use \Application\DeskPRO\Entity;

class Factory
{
	public static function createHandlersForWidgets($widgets, $context, array $options = array())
	{
		$handlers = array();

		foreach ($widgets as $w) {
			$handlers[$w['id']] = $w->getHandler($options);
		}

		return $handlers;
	}
}