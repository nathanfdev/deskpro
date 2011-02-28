<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Translate
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Translate;

/**
 * A fake locale used in the translate class as the 'base'.
 * The translate system uses this to load from the filesystem only.
 */
class SystemLocale extends \Application\DeskPRO\Entity\Locale
{
	protected static $instance = null;
	public static function getInstance()
	{
		if (self::$instance !== null) return self::$instance;

		self::$instance = new self();

		return self::$instance;
	}

	protected function __construct()
	{
		$this->id = 0;
		$this->locale = 'en_US';
		$this->title = "English (US)";

		$this->language = new \Application\DeskPRO\Entity\Language();
		$this->language['id'] = 0;
		$this->language['title'] = 'English';
	}
}