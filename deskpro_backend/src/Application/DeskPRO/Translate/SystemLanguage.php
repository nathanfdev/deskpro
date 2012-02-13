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
 * A fake language in the translate class etc
 */
class SystemLanguage extends \Application\DeskPRO\Entity\Language
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
		$this->language_package = 'DeskproLanguages\\DeskPRO\\LangPackage';
	}
}
