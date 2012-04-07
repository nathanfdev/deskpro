<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage DeskproLanguages
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace DeskproLanguages;

use Orb\Util\Util;

class LangPackage
{
	/**
	 * Get the version of this lang pack
	 *
	 * @return mixed
	 */
	public static function getVersion()
	{
		return '0.0.1';
	}


	/**
	 * Get the version of DeskPRO this lang pack was designed for
	 *
	 * @return mixed
	 */
	public static function getSourceVersion()
	{
		return '0000-00-00 00:00:00';
	}


	/**
	 * Get the default locale used with the language
	 *
	 * @return string
	 */
	public static function getLocale()
	{
		return 'en_US';
	}


	/**
	 * Get the unique name for the lang
	 *
	 * @return string
	 */
	public static function getName()
	{
		return str_replace('\\', '_', Util::getClassNamespace(get_called_class()));
	}


	/**
	 * Get the readable title for this plugin
	 *
	 * @return string
	 */
	public static function getTitle()
	{
		return 'English (US)';
	}


	/**
	 * Get the readable description for this plugin
	 *
	 * @return string
	 */
	public static function getDescription()
	{
		return '';
	}


	/**
	 * Get the path to the lang file directory
	 *
	 * @return string
	 */
	public static function getLangPath()
	{
		$path = dirname(Util::getClassFilename(get_called_class()));
		return $path;
	}
}
