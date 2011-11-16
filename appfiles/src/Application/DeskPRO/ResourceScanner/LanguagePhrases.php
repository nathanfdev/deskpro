<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\ResourceScanner;

use Application\DeskPRO\App;
use Orb\Util\Arrays;

class LanguagePhrases
{
	/**
	 * @var string
	 */
	protected $lang_root;

	public function __construct($lang_root = null)
	{
		if ($lang_root === null) {
			$lang_root = DP_ROOT.'/languages/DeskPRO';
		}

		$this->lang_root = $lang_root;
	}

	public function getGroups()
	{
		$groups = array();

		$lang_dir = dir($this->lang_root);
		while (($dir_name = $lang_dir->read()) !== false) {
			if ($dir_name == '.' || $dir_name == '..') continue;

			$dir_path = $lang_dir->path . DIRECTORY_SEPARATOR . $dir_name;
			if (!is_dir($dir_path)) continue;

			$dir = dir($dir_path);

			while (($file = $dir->read()) != false) {
				if ($file == '.' || $file == '..') continue;

				if (!isset($groups[$dir_name])) $groups[$dir_name] = array();
				$groups[$dir_name][] = str_replace('.php', '', $file);
			}
		}

		return $groups;
	}

	public function getGroupPhrases($group)
	{
		$file = str_replace('.', DIRECTORY_SEPARATOR, $group) . '.php';
		$filepath = $this->lang_root . DIRECTORY_SEPARATOR . $file;

		return include($filepath);
	}
}
