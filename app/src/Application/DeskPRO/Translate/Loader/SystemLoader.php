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
 * @category Translate
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
		$lang_packs[] = 'DeskproLanguages\\LangPackage';

		if ($language) {
			$lang_packs[] = $language->getLanguagePackage();
		}

		$lang_packs = array_unique($lang_packs);
		$lang_packs = Arrays::removeFalsey($lang_packs);

		$phrases = array();

		foreach ($lang_packs as $pack_class) {

			$ns_parts = explode('\\', $pack_class);

            if(count($ns_parts) == 3) {
                $file = DP_ROOT . '/languages/' . $ns_parts[1] . '/LangPackage.php';
			}
            else {
                $file = DP_ROOT . '/languages/LangPackage.php';
            }


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
