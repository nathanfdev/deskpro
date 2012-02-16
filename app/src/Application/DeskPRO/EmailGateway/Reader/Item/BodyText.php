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

class BodyText
{
	public $body;
	public $body_utf8;
	public $original_charset;

	public function getBody()
	{
		return $this->body;
	}

	public function getBodyUtf8()
	{
		return $this->body_utf8;
	}

	public function getOriginalCharset()
	{
		return $this->original_charset;
	}
}
