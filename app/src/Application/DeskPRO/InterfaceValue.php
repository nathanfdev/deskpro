<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO;

class InterfaceValue
{
	public function getInterface()
	{
		return defined('DP_INTERFACE') ? DP_INTERFACE : 'user';
	}

	public function __toString()
	{
		return $this->getInterface();
	}
}
