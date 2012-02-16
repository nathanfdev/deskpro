<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EmailGateway\Cutter;

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;

class CutterDefFactory
{
	public static function getDef(AbstractReader $reader)
	{
		$def = new Def\Generic();
		return $def;
	}
}
