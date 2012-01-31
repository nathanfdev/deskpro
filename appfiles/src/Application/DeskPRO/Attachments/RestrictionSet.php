<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category DependencyInjection
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Attachments;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Orb\Util\Strings;

/**
 * Various tests that can be run on a file to see if we should accept it.
 */
class RestrictionSet
{
	const ERR_SIZE = 'size';
	const ERR_BAD_EXT = 'ext';

	/**
	 * The max size to accept
	 *
	 * @var int
	 */
	protected $max_size = 5242880; // 5 MB

	/**
	 * Whitelist of extention to accept
	 *
	 * @var array
	 */
	protected $allowed_exts = null;

	/**
	 * Blacklist of extensions to reject
	 *
	 * @var array
	 */
	protected $disallowed_exts = null;


	/**
	 * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
	 * @return array|null
	 */
	public function getError(UploadedFile $file)
	{
		$size = $file->getSize();
		$ext  = Strings::getExtension($file->getClientOriginalName());

		if ($this->max_size && $size > $this->max_size) {
			return array(
				'error_code' => self::ERR_SIZE,
				'error_detail' => $this->max_size
			);
		}

		if ($this->allowed_exts && !in_array($ext, $this->allowed_exts)) {
			return array(
				'error_code' => self::ERR_BAD_EXT,
				'error_detail' => implode(',', $this->allowed_exts)
			);
		}

		if ($this->disallowed_exts && in_array($ext, $this->disallowed_exts)) {
			return array(
				'error_code' => self::ERR_BAD_EXT,
				'error_detail' => implode(',', $this->disallowed_exts)
			);
		}

		return null;
	}


	/**
	 * @param array $allowed_exts
	 */
	public function setAllowedExts(array $allowed_exts = null)
	{
		$this->allowed_exts = $allowed_exts;
		return $this;
	}


	/**
	 * @return array
	 */
	public function getAllowedExts()
	{
		return $this->allowed_exts;
	}


	/**
	 * @param array $disallowed_exts
	 */
	public function setDisallowedExts(array $disallowed_exts = null)
	{
		$this->disallowed_exts = $disallowed_exts;
		return $this;
	}


	/**
	 * @return array
	 */
	public function getDisallowedExts()
	{
		return $this->disallowed_exts;
	}


	/**
	 * @param int $max_size
	 */
	public function setMaxSize($max_size = null)
	{
		$this->max_size = (int)$max_size;
		return $this;
	}


	/**
	 * @return int
	 */
	public function getMaxSize()
	{
		return $this->max_size;
	}
}
