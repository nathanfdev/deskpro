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

namespace Application\DeskPRO\Translate\Loader;

use Orb\Util\Arrays;

/**
 * Loads default phrases from filesystem-based lang packs
 */
class SystemLoader implements LoaderInterface
{
	protected $file_path;

	/**
	 * @param string $file_path The base path where language packs are kept
	 */
	public function __construct($file_path)
	{
		$this->file_path = $file_path;
	}

	public function load($groups, $language)
	{
		$lang_packs = array();

		// Always read from the default because it has the core phrases
		$lang_packs[] = 'DeskproLanguages\\DeskPRO\\LangPackage';

		if ($language) {
			$lang_packs[] = $language->getLanguagePackage();
		}

		$lang_packs = array_unique($lang_packs);
		$lang_packs = Arrays::removeFalsey($lang_packs);

		$phrases = array();

		foreach ($lang_packs as $pack_class) {

			$ns_parts = explode('\\', $pack_class);
			$file = DP_ROOT . '/languages/' . $ns_parts[1] . '/LangPackage.php';
			if (!file_exists($file)) {
				continue;
			}

			require_once($file);

			$path = $pack_class::getLangPath();

			foreach ($groups as $group) {

				if (!isset($phrases[$group])) {
					$phrases[$group] = array();
				}

				$group_parts = explode('.', $group, 2);

				// agent.something => agent/something.php
				if (count($group_parts) == 2) {
					$file = $path . '/' . $group_parts[0] . '/' . $group_parts[1] . '.php';
				// agent => agent/agent.php
				} else {
					$file = $path . '/' . $group_parts[0] . '/' . $group_parts[0] . '.php';
				}

				if (is_file($file)) {
					$file_phrases = include($file);
					if ($file_phrases && is_array($file_phrases)) {
						$phrases[$group] = array_merge($phrases[$group], $file_phrases);
					}
				}
			}
		}

		return $phrases;
	}
}
