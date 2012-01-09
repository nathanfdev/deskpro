<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Twig
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Twig;

class Environment extends \Twig_Environment
{
	public function __construct(\Twig_LoaderInterface $loader = null, $options = array())
	{
		if (defined('DP_DEBUG') && (empty($options['auto_reload']) || $options['auto_reload'] === null)) {
			if (DP_DEBUG) {
				$options['auto_reload'] = true;
			} else {
				$options['auto_reload'] = false;
			}
		}

		parent::__construct($loader, $options);
	}
}
