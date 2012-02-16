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

class LanguagePacks
{
	/**
	 * @var string
	 */
	protected $pack_root;

	public function __construct($pack_root = null)
	{
		if ($pack_root === null) {
			$pack_root = DP_ROOT.'/languages';
		}

		$this->pack_root = $pack_root;
	}

	public function getPacks()
	{
		$pack_root = str_replace(DIRECTORY_SEPARATOR, '/', $this->pack_root);

		$finder = new \Symfony\Component\Finder\Finder();
		$finder->files()->name('LangPackage.php')->in(array($pack_root));

		$packs = array();

		foreach ($finder as $file) {
			$class = str_replace(DIRECTORY_SEPARATOR, '/', $file->getPathname());
			$class = str_replace($pack_root, '', $class);
			$class = str_replace('/', '\\', $class);
			$class = str_replace('.php', '', $class);
			$class = 'DeskproLanguages' . $class;

			require_once($file->getPathname());
			$name = $class::getTitle();

			$packs[$class] = $name;
		}

		return $packs;
	}
}
