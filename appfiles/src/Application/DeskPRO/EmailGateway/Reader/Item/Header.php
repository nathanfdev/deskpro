<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EmailGateway\Reader\Item;

use Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

class Header
{
	public $name;
	public $header_parts;

	public function getName()
	{
		return $name;
	}

	public function getHeader()
	{
		return $this->header_parts[0];
	}

	public function getAllParts()
	{
		return $this->header_parts;
	}
}