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

class Attachment
{
	public $tmp_file;
	public $file_contents_callback;
	public $file_contents;
	public $file_name;
	public $mime_type;

	public function getFileContents()
	{
		if ($this->file_contents) {
			return $this->file_contents;
		} elseif ($this->file_contents_callback) {
			return call_user_func($this->file_contents_callback, $this);
		} else {
			return file_get_contents($this->tmp_file);
		}
	}

	public function getFileName()
	{
		return $this->file_name;
	}

	public function getMimeType()
	{
		return $this->mime_type;
	}
}