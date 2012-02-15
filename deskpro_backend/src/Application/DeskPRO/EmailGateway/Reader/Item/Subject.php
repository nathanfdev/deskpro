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

class Subject extends Header
{
	public $subject;
	public $subject_utf8;
	public $original_charset;

	public function getSubject()
	{
		return $this->subject;
	}

	public function getSubjectUtf8()
	{
		return $this->subject_utf8;
	}

	public function getOriginalCharset()
	{
		return $this->original_charset;
	}
}
