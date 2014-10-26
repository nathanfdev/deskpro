<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\EmailGateway\Reader\Item;

use Application\DeskPRO\App;

class Attachment
{
	/** @var string */
	public $tmp_file;
	/** @var Callable */
	public $file_contents_callback;
	/** @var string|null */
	public $file_contents = null;
	/** @var string */
	public $file_name;
	/** @var string */
	public $file_name_utf8;
	/** @var string */
	public $mime_type;
	/** @var string */
	public $original_charset; // with Rfc822 types
	/** @var string ? */
	public $content_id;

	public function getFileContents()
	{
		if ($this->file_contents !== null) {
			return $this->file_contents;
		} elseif ($this->file_contents_callback) {
			return call_user_func($this->file_contents_callback, $this);
		} elseif ($this->tmp_file) {
			return file_get_contents($this->tmp_file);
		} else {
			return '';
		}
	}

	public function getFileName()
	{
		return $this->file_name;
	}

	public function getFileNameUtf8()
	{
		if (!$this->file_name_utf8) {
			return $this->getFileName();
		}

		return $this->file_name_utf8;
	}

	public function getMimeType()
	{
		return $this->mime_type;
	}

	public function getContentId()
	{
		return $this->content_id;
	}
}
