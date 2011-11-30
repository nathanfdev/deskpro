<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Routing;

class Router extends \Symfony\Bundle\FrameworkBundle\Routing\Router
{
	public function setOptions(array $options)
	{
		if (isset($options['debug'])) {
			$options['debug'] = false;
		}

		return parent::setOptions($options);
	}
}
