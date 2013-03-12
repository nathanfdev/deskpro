<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

namespace Application\DeskPRO\BlobStorage;

class Blob
{
	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var string
	 */
	protected $filename;

	/**
	 * @var string
	 */
	protected $content_type;

	/**
	 * @var array
	 */
	protected $meta;

	public function __construct($path, $filename, $content_type, array $meta = array())
	{
		$this->path = $path;
		$this->filename = $filename;
		$this->content_type = $content_type;
		$this->meta = $meta;
	}


	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}


	/**
	 * @return string
	 */
	public function getContentType()
	{
		return $this->content_type;
	}


	/**
	 * @param string $id
	 * @param mixed $value
	 */
	public function setMeta($id, $value)
	{
		if ($value === null) {
			unset($this->meta[$value]);
		} else {
			$this->meta[$id] = $value;
		}
	}

	/**
	 * @param $id
	 * @param null $default
	 * @return null
	 */
	public function getMeta($id, $default = null)
	{
		return isset($this->meta[$id]) ? $this->meta[$id] : $default;
	}


	/**
	 * @return array
	 */
	public function getAllMeta()
	{
		return $this->meta;
	}
}